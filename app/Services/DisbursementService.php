<?php

namespace App\Services;

use App\Models\FundDisbursement;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use App\Services\NumberingService;
use Carbon\Carbon;

class DisbursementService
{
    protected $ledgerService;
    protected $siklusService;
    protected $numberingService;
    protected $bankService;

    public function __construct(
        CashLedgerService $ledgerService,
        SiklusService $siklusService,
        NumberingService $numberingService,
        BankLedgerService $bankService
    ) {
        $this->ledgerService = $ledgerService;
        $this->siklusService = $siklusService;
        $this->numberingService = $numberingService;
        $this->bankService = $bankService;
    }

    /**
     * Store a new Fund Disbursement.
     */
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $intendedStatus = $data['status'] ?? 'SPP';
            // Validation: Saldo Rekening Koran Check First - Only for LS as it deducts from balance
            if ($intendedStatus === 'CAIR' && $data['type'] === 'LS') {
                $bankBalance = $this->bankService->getCurrentBalance();
                if ($bankBalance < $data['value']) {
                    throw new \Exception("Gagal: Saldo Rekening Koran tidak cukup! Sisa: Rp " . number_format($bankBalance, 0, ',', '.'));
                }
            }

            // Strict Integrity Guard: For activity-based disbursements, reserve cash must be available.
            $intendedStatus = $data['status'] ?? 'SPP';
            $isActivity = !empty($data['kode_rekening_id']) || !empty($data['expenditure_id']);

            if ($intendedStatus !== 'DRAFT' && $isActivity) {
                $checkDate = $data['sp2d_date'] ?? now();
                $year = \Carbon\Carbon::parse($checkDate)->year;
                $available = $this->ledgerService->getAvailableLiquidity($year, true);

                if ($data['value'] > $available) {
                    throw new \Exception("Transaksi menyebabkan saldo kas menjadi negatif");
                }
            }

            $date = !empty($data['sp2d_date']) ? $data['sp2d_date'] : now();
            $year = \Carbon\Carbon::parse($date)->year;

            $data['tahun'] = $year;

            if ($data['type'] === 'UP') {
                $data['siklus_up'] = $data['siklus_up'] ?? $this->siklusService->startNewSiklus($year);
                $data['nomor_dalam_siklus'] = 1;
            } elseif ($data['type'] === 'GU') {
                if (empty($data['siklus_up'])) {
                    $spjId = $data['spj_id'] ?? null;
                    $existing = null;
                    if ($spjId) {
                        $existing = FundDisbursement::where('type', 'GU')->where('spj_id', $spjId)->first();
                    }

                    if ($existing) {
                        $data['siklus_up'] = $existing->siklus_up;
                    } else {
                        $lastGu = FundDisbursement::where('tahun', $year)->where('type', 'GU')->max('siklus_up') ?? 0;
                        $data['siklus_up'] = $lastGu + 1;
                    }
                }
                $data['nomor_dalam_siklus'] = $this->siklusService->getNextNomorDalamSiklus($year, $data['siklus_up'], 'GU');
            } elseif ($data['type'] === 'LS') {
                $data['siklus_up'] = null;
                // Only assign LS-X sequence if it's an SPP/SPM process (not direct Saldo Dana)
                if ($intendedStatus !== 'CAIR') {
                    $data['nomor_dalam_siklus'] = $this->siklusService->getNextNomorDalamSiklus($year, 0, 'LS');
                } else {
                    $data['nomor_dalam_siklus'] = 0;
                }
            }

            // Always assign a global paket number during creation
            $data['nomor_paket'] = $this->numberingService->generatePaketNumber($year, $data['type'], $data['siklus_up'] ?? 0, $data['nomor_dalam_siklus']);

            // Initial status handling - default to SPP
            $intendedStatus = $data['status'] ?? 'SPP';
            $data['status'] = $intendedStatus;

            $disbursement = FundDisbursement::create($data);

            // Generate numbers based on the initial status
            $isDirectSaldo = ($intendedStatus === 'CAIR');

            if (!$isDirectSaldo) {
                if (in_array($intendedStatus, ['SPP', 'SPM', 'CAIR'])) {
                    $res = $this->numberingService->generateSppNumber($year, $disbursement->type, $disbursement->siklus_up, $disbursement->sp2d_date, $disbursement->nomor_dalam_siklus);
                    $disbursement->spp_no = $res['formatted'];
                    $disbursement->spp_urut = $res['urut'];
                }

                if (in_array($intendedStatus, ['SPM', 'CAIR'])) {
                    $res = $this->numberingService->generateSpmNumber($year, $disbursement->type, $disbursement->siklus_up, $disbursement->sp2d_date, $disbursement->nomor_dalam_siklus);
                    $disbursement->spm_no = $res['formatted'];
                    $disbursement->spm_urut = $res['urut'];
                }

                if ($intendedStatus === 'CAIR') {
                    $res = $this->numberingService->generateSp2dNumber($year);
                    $disbursement->sp2d_no = $res['formatted'];
                    $disbursement->sp2d_urut = $res['urut'];
                    $disbursement->sp2d_date = $disbursement->sp2d_date ?? $date;
                    $disbursement->number_locked_at = now();
                }
            } else {
                if ($intendedStatus === 'CAIR') {
                    $disbursement->number_locked_at = now();
                }
            }

            // Generate Proof Number (No Bukti) for activity-based disbursements
            if (!empty($disbursement->kode_rekening_id) || !empty($disbursement->expenditure_id)) {
                $bukti = $this->numberingService->generateNoBukti(
                    $disbursement->type,
                    $disbursement->sp2d_date,
                    $disbursement->nomor_dalam_siklus,
                    $disbursement->siklus_up ?? 0
                );
                $disbursement->no_bukti = $bukti['no_bukti'];
                $disbursement->no_bukti_urut = $bukti['no_bukti_urut'];
            }

            $disbursement->save();

            // Ledger impact only when CAIR or SPP/SPM (Pengajuan)
            if (in_array($intendedStatus, ['SPP', 'SPM', 'CAIR'])) {
                $this->applyLedgerImpact($disbursement);
            }

            ActivityLog::log('CREATE', 'DISBURSEMENT', "Mencatat pengajuan [{$disbursement->type}]: Package #{$disbursement->nomor_paket} - Status: {$intendedStatus}", $disbursement->id, null, $disbursement->toArray());

            return $disbursement;
        });
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $disbursement = FundDisbursement::lockForUpdate()->findOrFail($id);
            $oldData = $disbursement->toArray();

            // Check if value/date changed and needs integrity validation
            $newValue = $data['value'] ?? $disbursement->value;
            $newDate = $data['sp2d_date'] ?? $disbursement->sp2d_date;
            $newStatus = $data['status'] ?? $disbursement->status;

            if ($newStatus !== 'DRAFT' && ($disbursement->kode_rekening_id || $disbursement->expenditure_id)) {
                $year = \Carbon\Carbon::parse($newDate)->year;
                $available = $this->ledgerService->getAvailableLiquidity($year, true, null, $id);
                if ($newValue > $available) {
                    throw new \Exception("Transaksi menyebabkan saldo kas menjadi negatif");
                }
            }

            $disbursement->update($data);

            // If it was already SPP or higher, we might need to update ledger or numbering
            // But for now we just log it. General applyLedgerImpact handles changes.
            if (in_array($disbursement->status, ['SPP', 'SPM', 'CAIR'])) {
                $this->applyLedgerImpact($disbursement);
            }

            ActivityLog::log('UPDATE', 'DISBURSEMENT', "Mengubah pengajuan: #{$disbursement->nomor_paket}", $id, $oldData, $disbursement->toArray());

            // Global Liquidity Safety Net
            $this->ledgerService->validateGlobalLiquidity(\Carbon\Carbon::parse($disbursement->sp2d_date ?? now())->year);

            return $disbursement;
        });
    }

    /**
     * Update status and generate corresponding numbers.
     */
    public function updateStatus($id, $newStatus, $manualData = [])
    {
        return DB::transaction(function () use ($id, $newStatus, $manualData) {
            $disbursement = FundDisbursement::lockForUpdate()->findOrFail($id);
            $year = $disbursement->tahun;

            // Integrity Guard for status change
            if ($newStatus !== 'DRAFT' && ($disbursement->kode_rekening_id || $disbursement->expenditure_id)) {
                $available = $this->ledgerService->getAvailableLiquidity($year, true, null, $id);
                if ($disbursement->value > $available) {
                    throw new \Exception("Transaksi menyebabkan saldo kas menjadi negatif");
                }
            }

            // Handle manual status override from $manualData
            if ($newStatus === 'SPP' && !$disbursement->spp_no) {
                if (!empty($manualData['spp_no'])) {
                    $disbursement->spp_no = $manualData['spp_no'];
                } else {
                    $res = $this->numberingService->generateSppNumber($year, $disbursement->type, $disbursement->siklus_up, $disbursement->sp2d_date, $disbursement->nomor_dalam_siklus);
                    $disbursement->spp_no = $res['formatted'];
                    $disbursement->spp_urut = $res['urut'];
                }
            }

            if ($newStatus === 'SPM' && !$disbursement->spm_no) {
                if (!$disbursement->spp_no) {
                    $resSpp = $this->numberingService->generateSppNumber($year, $disbursement->type, $disbursement->siklus_up, $disbursement->sp2d_date, $disbursement->nomor_dalam_siklus);
                    $disbursement->spp_no = $resSpp['formatted'];
                    $disbursement->spp_urut = $resSpp['urut'];
                }

                if (!empty($manualData['spm_no'])) {
                    $disbursement->spm_no = $manualData['spm_no'];
                } else {
                    $res = $this->numberingService->generateSpmNumber($year, $disbursement->type, $disbursement->siklus_up, $disbursement->sp2d_date, $disbursement->nomor_dalam_siklus);
                    $disbursement->spm_no = $res['formatted'];
                    $disbursement->spm_urut = $res['urut'];
                }
            }

            if ($newStatus === 'CAIR' && !$disbursement->sp2d_no) {
                // Validation: Saldo Rekening Koran Check First - ONLY for LS
                if ($disbursement->type === 'LS') {
                    $bankBalance = $this->bankService->getCurrentBalance();
                    if ($bankBalance < $disbursement->value) {
                        throw new \Exception("Gagal Cairkan: Saldo Rekening Koran tidak cukup! Sisa saldo mu: Rp " . number_format($bankBalance, 0, ',', '.'));
                    }
                }

                if (!$disbursement->spp_no) {
                    $resSpp = $this->numberingService->generateSppNumber($year, $disbursement->type, $disbursement->siklus_up, $disbursement->sp2d_date, $disbursement->nomor_dalam_siklus);
                    $disbursement->spp_no = $resSpp['formatted'];
                    $disbursement->spp_urut = $resSpp['urut'];
                }
                if (!$disbursement->spm_no) {
                    $resSpm = $this->numberingService->generateSpmNumber($year, $disbursement->type, $disbursement->siklus_up, $disbursement->sp2d_date, $disbursement->nomor_dalam_siklus);
                    $disbursement->spm_no = $resSpm['formatted'];
                    $disbursement->spm_urut = $resSpm['urut'];
                }

                if (!empty($manualData['sp2d_no'])) {
                    $disbursement->sp2d_no = $manualData['sp2d_no'];
                } else {
                    $res = $this->numberingService->generateSp2dNumber($year);
                    $disbursement->sp2d_no = $res['formatted'];
                    $disbursement->sp2d_urut = $res['urut'];
                }

                $disbursement->sp2d_date = $disbursement->sp2d_date ?? now();
                $disbursement->number_locked_at = now();
                $disbursement->status = $newStatus; // update status first before calling applyLedgerImpact

                // Generate Proof Number (No Bukti) if missing for activity-based disbursements
                if (!$disbursement->no_bukti && (!empty($disbursement->kode_rekening_id) || !empty($disbursement->expenditure_id))) {
                    $bukti = $this->numberingService->generateNoBukti($disbursement->type, $disbursement->sp2d_date, $disbursement->nomor_dalam_siklus, $disbursement->siklus_up ?? 0);
                    $disbursement->no_bukti = $bukti['no_bukti'];
                    $disbursement->no_bukti_urut = $bukti['no_bukti_urut'];
                }

                $disbursement->save(); // Save to make sure we don't have mismatch issues if ledger needs saved data
                $this->applyLedgerImpact($disbursement);
                return $disbursement;
            } else {
                $disbursement->status = $newStatus;

                // Generate Proof Number (No Bukti) if missing for activity-based disbursements
                if (!$disbursement->no_bukti && (!empty($disbursement->kode_rekening_id) || !empty($disbursement->expenditure_id))) {
                    $bukti = $this->numberingService->generateNoBukti($disbursement->type, $disbursement->sp2d_date, $disbursement->nomor_dalam_siklus, $disbursement->siklus_up ?? 0);
                    $disbursement->no_bukti = $bukti['no_bukti'];
                    $disbursement->no_bukti_urut = $bukti['no_bukti_urut'];
                }

                $disbursement->save();
            }

            // Apply Ledger Impact for the new status
            $this->applyLedgerImpact($disbursement);

            // Global Liquidity Safety Net
            $this->ledgerService->validateGlobalLiquidity($disbursement->tahun);

            return $disbursement;
        });
    }

    private function generateFinalNumbers(FundDisbursement $disbursement)
    {
        $year = $disbursement->tahun;
        $date = $disbursement->sp2d_date;
        $type = $disbursement->type;
        $siklus = $disbursement->siklus_up;

        if (!$disbursement->spp_no) {
            $res = $this->numberingService->generateSppNumber($year, $type, $siklus, $date, $disbursement->nomor_dalam_siklus);
            $disbursement->spp_no = $res['formatted'];
            $disbursement->spp_urut = $res['urut'];
        }
        if (!$disbursement->spm_no) {
            $res = $this->numberingService->generateSpmNumber($year, $type, $siklus, $date, $disbursement->nomor_dalam_siklus);
            $disbursement->spm_no = $res['formatted'];
            $disbursement->spm_urut = $res['urut'];
        }
        if (!$disbursement->sp2d_no) {
            $res = $this->numberingService->generateSp2dNumber($year);
            $disbursement->sp2d_no = $res['formatted'];
            $disbursement->sp2d_urut = $res['urut'];
        }
        $disbursement->number_locked_at = now();
    }

    private function applyLedgerImpact(FundDisbursement $disbursement)
    {
        $date = $disbursement->sp2d_date ?: now();
        $id = $disbursement->id;
        $isActivity = !empty($disbursement->kode_rekening_id) || !empty($disbursement->expenditure_id);

        // Determine logical document number for grouping in Bank Ledger
        $refNo = $disbursement->sp2d_no ?: ($disbursement->spm_no ?: $disbursement->spp_no);

        // Calculate total value for this document grouping (to support multiple activities in one SP2D)
        $totalVal = $disbursement->value;
        if ($refNo) {
            $totalVal = FundDisbursement::where(function ($q) use ($refNo) {
                $table = (new FundDisbursement)->getTable();
                $q->where("{$table}.sp2d_no", $refNo)->orWhere("{$table}.spm_no", $refNo)->orWhere("{$table}.spp_no", $refNo);
            })->sum('value');
        }

        if ($disbursement->type === 'LS') {
            if ($disbursement->status !== 'CAIR')
                return;
            $desc = $disbursement->uraian ?: ($disbursement->description ?: ($disbursement->sp2d_no ?? $disbursement->nomor_paket));
            $type = $isActivity ? 'ACTIVITY_LS' : 'LS_RECEIPT';
            $this->ledgerService->recordEntry($date, $type, $disbursement->value, 'fund_disbursements', $id, 'DEBIT', $desc);
            $this->bankService->recordEntry($date, 'WITHDRAW_LS', $totalVal, 'fund_disbursements', $id, 'CREDIT', "Penarikan SP2D LS " . ($refNo ?? 'Manual'), $refNo);
        } else {
            // UP or GU
            if ($isActivity) {
                // ACTIVITY-BASED: These are SPP line items tied to a Kode Rekening
                // They only create a TRACE entry in BKU; actual spending is through Expenditures
                if (in_array($disbursement->status, ['SPP', 'SPM', 'CAIR'])) {
                    $this->ledgerService->recordEntry($date, "TRACE_ACTIVITY_{$disbursement->type}", 0, 'fund_disbursements', $id, 'DEBIT', "[Audit Trace] " . ($disbursement->uraian ?: ($disbursement->description ?: "Kegiatan {$disbursement->type}")));
                } else {
                    $this->ledgerService->removeEntry('fund_disbursements', $id);
                    $this->bankService->removeEntry('fund_disbursements', $id);
                }
            } else {
                // SALDO DANA (Non-activity): This is a cash refill (UP/GU)
                // When CAIR: Money flows INTO the Treasurer Cash (DEBIT only)
                // No CREDIT here — the CREDIT happens when Expenditures are recorded
                if (in_array($disbursement->status, ['SPP', 'SPM', 'CAIR'])) {
                    $type = "AJU_{$disbursement->type}";
                    $desc = ($disbursement->uraian ?: ($disbursement->description ?: "Isi Saldo Kas {$disbursement->type}"));

                    // Debit BKU (Increases Saldo Dana)
                    $this->ledgerService->recordEntry($date, $type, $disbursement->value, 'fund_disbursements', $id, 'DEBIT', $desc);

                    // Decreases Rekening Koran (Moves money from Bank to Cash/Dana)
                    $this->bankService->recordEntry($date, "WITHDRAW_{$disbursement->type}", $totalVal, 'fund_disbursements', $id, 'CREDIT', "Penarikan SP2D {$disbursement->type} ({$refNo})", $refNo);
                } else {
                    // If status reverted back to DRAFT, remove everything
                    $this->ledgerService->removeEntry('fund_disbursements', $id);
                    $this->bankService->removeEntry('fund_disbursements', $id);
                }
            }
        }
    }

    /**
     * Revert a disbursement status one step back.
     * CAIR -> SPM: removes SP2D number and ledger impact
     * SPM -> SPP: removes SPM number
     */
    public function revertStatus($id, $targetStatus)
    {
        return DB::transaction(function () use ($id, $targetStatus) {
            $disbursement = FundDisbursement::findOrFail($id);
            $oldData = $disbursement->toArray();
            $currentStatus = $disbursement->status;

            // Validate transition
            $allowedRevert = [
                'CAIR' => 'SPM',
                'SPM' => 'SPP',
            ];

            if (!isset($allowedRevert[$currentStatus]) || $allowedRevert[$currentStatus] !== $targetStatus) {
                throw new \Exception("Pembatalan dari {$currentStatus} ke {$targetStatus} tidak diizinkan.");
            }

            if ($currentStatus === 'CAIR') {
                // Check if there are any linked expenditures at all
                $linkedCount = \App\Models\Expenditure::where('fund_disbursement_id', $disbursement->id)->count();

                if ($linkedCount > 0) {
                    // Check specifically for SPJ-linked ones
                    $linkedInSpj = \App\Models\Expenditure::where('fund_disbursement_id', $disbursement->id)
                        ->whereHas('spjItems')
                        ->count();

                    if ($linkedInSpj > 0) {
                        throw new \Exception("Tidak bisa membatalkan SP2D karena ada {$linkedInSpj} belanja yang sudah masuk dalam SPJ. Hapus dari SPJ terlebih dahulu.");
                    }

                    throw new \Exception("Tidak bisa membatalkan SP2D karena masih ada {$linkedCount} rincian belanja. Hapus semua belanja terlebih dahulu sebelum membatalkan.");
                }

                // Remove ledger impact for the disbursement itself

                $this->ledgerService->removeEntry('fund_disbursements', $disbursement->id);
                $this->bankService->removeEntry('fund_disbursements', $disbursement->id);

                // Clear SP2D data
                $disbursement->sp2d_no = null;
                $disbursement->sp2d_urut = null;
                $disbursement->number_locked_at = null;
            }

            if ($currentStatus === 'SPM') {
                // Clear SPM data
                $disbursement->spm_no = null;
                $disbursement->spm_urut = null;
            }

            $disbursement->status = $targetStatus;
            $disbursement->save();

            ActivityLog::log('REVERT', 'DISBURSEMENT', "Membatalkan {$currentStatus} -> {$targetStatus}: {$disbursement->type} #{$disbursement->nomor_paket}", $disbursement->id, $oldData, $disbursement->toArray());

            // Global Liquidity Safety Net
            $this->ledgerService->validateGlobalLiquidity($disbursement->tahun);

            return $disbursement;
        });
    }

    /**
     * Delete a disbursement.
     */
    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $disbursement = FundDisbursement::findOrFail($id);

            // Check if any linked expenditures are in SPJ
            $linkedInSpj = \App\Models\Expenditure::where('fund_disbursement_id', $disbursement->id)
                ->whereHas('spjItems')
                ->count();

            if ($linkedInSpj > 0) {
                throw new \Exception("Tidak bisa menghapus pencairan karena ada {$linkedInSpj} belanja yang sudah masuk dalam SPJ.");
            }

            $oldData = $disbursement->toArray();
            $label = $disbursement->type . " #" . ($disbursement->sp2d_no ?? $disbursement->nomor_paket);

            // Delete all linked expenditures and their ledger entries
            $linkedExpenditures = \App\Models\Expenditure::where('fund_disbursement_id', $disbursement->id)->get();
            foreach ($linkedExpenditures as $exp) {
                /** @var \App\Models\Expenditure $exp */
                $this->ledgerService->removeEntry('expenditures', $exp->id);
                $this->bankService->removeEntry('expenditures', $exp->id);
                ActivityLog::log('DELETE', 'EXPENDITURE', "Otomatis dihapus karena penghapusan pencairan: {$exp->description}", $exp->id, $exp->toArray());
                $exp->delete();
            }

            // hapus ledger BKU (jika ada)
            $this->ledgerService->removeEntry('fund_disbursements', $disbursement->id);

            // hapus ledger Rekening (jika ada)
            $this->bankService->removeEntry('fund_disbursements', $disbursement->id);

            $disbursement->delete();

            $deletedCount = $linkedExpenditures->count();
            $extraLog = $deletedCount > 0 ? " ({$deletedCount} belanja ikut dihapus)" : "";
            ActivityLog::log('DELETE', 'DISBURSEMENT', "Menghapus pencairan: {$label}{$extraLog}", $id, $oldData);

            // Global Liquidity Safety Net
            $this->ledgerService->validateGlobalLiquidity($disbursement->tahun);

            return true;
        });
    }
}
