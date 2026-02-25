<?php

namespace App\Services;

use App\Models\FundDisbursement;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use App\Services\NumberingService;

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

            // Validation: Saldo Kas UP/GU check for activity SPPs
            if (in_array($data['type'], ['UP', 'GU']) && !empty($data['kode_rekening_id'])) {
                $year = \Carbon\Carbon::parse($data['sp2d_date'] ?? now())->year;
                $pType = $data['type'];
                $pSiklus = $data['siklus_up'] ?? null;

                $qCair = FundDisbursement::where('tahun', $year)->where('type', $pType)->where('status', 'CAIR');
                $qBelanja = \App\Models\Expenditure::whereYear('spending_date', $year)->where('spending_type', $pType);
                $qPending = FundDisbursement::where('tahun', $year)->where('type', $pType)->whereIn('status', ['SPP', 'SPM']);

                if ($pType === 'GU' && $pSiklus) {
                    $qCair->where('siklus_up', $pSiklus);
                    $qBelanja->where('siklus_up', $pSiklus);
                    $qPending->where('siklus_up', $pSiklus);
                }

                $totalCair = (float) (clone $qCair)->whereNull('spp_no')->sum('value');
                $sppKeluar = (float) (clone $qCair)->whereNotNull('spp_no')->sum('value');
                $totalBelanja = (float) $qBelanja->sum('gross_value') + $sppKeluar;
                $sppPending = (float) $qPending->where(function ($q) {
                    $q->whereNotNull('kode_rekening_id')->orWhereNotNull('expenditure_id');
                })->sum('value');

                $sisaKas = $totalCair - $totalBelanja - $sppPending;

                if ($data['value'] > $sisaKas) {
                    throw new \Exception("Gagal: Nominal pengajuan (Rp " . number_format($data['value'], 0, ',', '.') . ") melebihi Sisa Saldo Kas yang tersedia (Rp " . number_format($sisaKas, 0, ',', '.') . ")!");
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

            $disbursement->save();

            // Ledger impact only when CAIR
            if ($intendedStatus === 'CAIR') {
                $this->applyLedgerImpact($disbursement);
            }

            ActivityLog::log('CREATE', 'DISBURSEMENT', "Mencatat pengajuan [{$disbursement->type}]: Package #{$disbursement->nomor_paket} - Status: {$intendedStatus}", $disbursement->id, null, $disbursement->toArray());

            return $disbursement;
        });
    }

    /**
     * Update status and generate corresponding numbers.
     */
    public function updateStatus($id, $newStatus)
    {
        return DB::transaction(function () use ($id, $newStatus) {
            $disbursement = FundDisbursement::lockForUpdate()->findOrFail($id);
            $year = $disbursement->tahun;

            if ($newStatus === 'SPP' && !$disbursement->spp_no) {
                $res = $this->numberingService->generateSppNumber($year, $disbursement->type, $disbursement->siklus_up, $disbursement->sp2d_date, $disbursement->nomor_dalam_siklus);
                $disbursement->spp_no = $res['formatted'];
                $disbursement->spp_urut = $res['urut'];
            }

            if ($newStatus === 'SPM' && !$disbursement->spm_no) {
                if (!$disbursement->spp_no) {
                    $resSpp = $this->numberingService->generateSppNumber($year, $disbursement->type, $disbursement->siklus_up, $disbursement->sp2d_date, $disbursement->nomor_dalam_siklus);
                    $disbursement->spp_no = $resSpp['formatted'];
                    $disbursement->spp_urut = $resSpp['urut'];
                }
                $res = $this->numberingService->generateSpmNumber($year, $disbursement->type, $disbursement->siklus_up, $disbursement->sp2d_date, $disbursement->nomor_dalam_siklus);
                $disbursement->spm_no = $res['formatted'];
                $disbursement->spm_urut = $res['urut'];
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
                $res = $this->numberingService->generateSp2dNumber($year);
                $disbursement->sp2d_no = $res['formatted'];
                $disbursement->sp2d_urut = $res['urut'];
                $disbursement->sp2d_date = $disbursement->sp2d_date ?? now();
                $disbursement->number_locked_at = now();
                $disbursement->status = $newStatus; // update status first before calling applyLedgerImpact
                $disbursement->save(); // Save to make sure we don't have mismatch issues if ledger needs saved data

                // When officially CAIR, record in ledger
                $this->applyLedgerImpact($disbursement);
            } else {
                $disbursement->status = $newStatus;
                $disbursement->save();
            }

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
        if ($disbursement->status !== 'CAIR')
            return;

        $isActivity = !empty($disbursement->spp_no);
        $date = $disbursement->sp2d_date;
        $val = $disbursement->value;
        $id = $disbursement->id;

        if ($disbursement->type === 'UP') {
            $desc = $disbursement->uraian ?: ($disbursement->description ?: "Penerimaan UP - " . ($disbursement->sp2d_no ?? $disbursement->nomor_paket));
            $type = $isActivity ? 'ACTIVITY_UP' : 'TERIMA_UP';
            // UP adds to BKU (Debit)
            $this->ledgerService->recordEntry($date, $type, $val, 'fund_disbursements', $id, 'DEBIT', $desc);

            if (!$isActivity) {
                // Only manual "Saldo Dana" refill pulls from Bank (Credit)
                $this->bankService->recordEntry($date, 'WITHDRAW_UP', $val, 'fund_disbursements', $id, 'CREDIT', "Penarikan SP2D UP " . ($disbursement->sp2d_no ?? 'Manual'));
            } else {
                // Activities do not reduce Rekening Koran (already covered by initial UP draw)
                $this->bankService->removeEntry('fund_disbursements', $id);
            }
        } elseif ($disbursement->type === 'GU') {
            $desc = $disbursement->uraian ?: ($disbursement->description ?: "Pengajuan GU {$disbursement->siklus_up} - " . ($disbursement->sp2d_no ?? $disbursement->nomor_paket));
            $type = $isActivity ? 'ACTIVITY_GU' : 'GU';
            // GU adds to BKU (Debit)
            $this->ledgerService->recordEntry($date, $type, $val, 'fund_disbursements', $id, 'DEBIT', $desc);

            if (!$isActivity) {
                // Only manual "Saldo Dana" replenishment pulls from Bank (Credit)
                $this->bankService->recordEntry($date, 'WITHDRAW_GU', $val, 'fund_disbursements', $id, 'CREDIT', "Penarikan SP2D GU {$disbursement->siklus_up} " . ($disbursement->sp2d_no ?? 'Manual'));
            } else {
                // Activities do not reduce Rekening Koran
                $this->bankService->removeEntry('fund_disbursements', $id);
            }
        } elseif ($disbursement->type === 'LS') {
            $desc = $disbursement->uraian ?: ($disbursement->description ?: "Penerimaan LS - " . ($disbursement->sp2d_no ?? $disbursement->nomor_paket));
            $type = $isActivity ? 'ACTIVITY_LS' : 'LS_RECEIPT';
            // LS adds to BKU only for reporting (SP2D column), not total liquidity impact here
            // (Total balance impact for LS is handled by the individual Expenditure Note)
            $this->ledgerService->recordEntry($date, $type, $val, 'fund_disbursements', $id, 'DEBIT', $desc);
            $this->bankService->removeEntry('fund_disbursements', $id);
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
                // Remove ledger impact
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

            $oldData = $disbursement->toArray();
            $label = $disbursement->type . " #" . ($disbursement->sp2d_no ?? $disbursement->nomor_paket);

            // hapus ledger BKU (jika ada)
            $this->ledgerService->removeEntry('fund_disbursements', $disbursement->id);

            // hapus ledger Rekening (jika ada)
            $this->bankService->removeEntry('fund_disbursements', $disbursement->id);

            $disbursement->delete();

            ActivityLog::log('DELETE', 'DISBURSEMENT', "Menghapus pencairan: {$label}", $id, $oldData);

            return true;
        });
    }
}
