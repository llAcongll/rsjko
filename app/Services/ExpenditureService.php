<?php

namespace App\Services;

use App\Models\Expenditure;
use App\Models\AnggaranRekening;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpenditureService
{
    protected $ledgerService;
    protected $numberingService;
    protected $siklusService;

    public function __construct(
        CashLedgerService $ledgerService,
        NumberingService $numberingService,
        SiklusService $siklusService
    ) {
        $this->ledgerService = $ledgerService;
        $this->numberingService = $numberingService;
        $this->siklusService = $siklusService;
    }

    public function checkBudget($kodeRekeningId, $tanggal, $nominal, $excludeId = null)
    {
        $year = Carbon::parse($tanggal)->year;

        $anggaran = AnggaranRekening::where('tahun', $year)
            ->where('kode_rekening_id', $kodeRekeningId)
            ->sum('nilai');

        $query = Expenditure::whereYear('spending_date', $year)
            ->where('kode_rekening_id', $kodeRekeningId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $realisasiSaatIni = $query->sum('gross_value');
        $sisaAnggaran = (float) $anggaran - (float) $realisasiSaatIni;

        if ($nominal > $sisaAnggaran) {
            return [
                'isValid' => false,
                'sisa' => $sisaAnggaran,
                'message' => 'Nominal melebihi sisa anggaran (Sisa: Rp ' . number_format($sisaAnggaran, 0, ',', '.') . ')'
            ];
        }

        return ['isValid' => true];
    }

    /**
     * Check if the treasurer has enough UP balance.
     */
    public function checkLedgerBalance($tanggal, $nominal, $excludeId = null)
    {
        $year = Carbon::parse($tanggal)->year;
        // RACE CONDITION PREVENTION: Lock balance during check
        $currentBalance = (float) $this->ledgerService->getCurrentBalance($year, true);

        // Also deduct pending SPP/SPM from the balance to prevent overspending
        $pendingAmount = \App\Models\FundDisbursement::where('tahun', $year)
            ->whereIn('status', ['SPP', 'SPM'])
            ->whereIn('type', ['UP', 'GU'])
            ->where(function ($q) {
                $q->whereNotNull('kode_rekening_id')->orWhereNotNull('expenditure_id');
            })
            ->sum('value');

        $currentBalance -= (float) $pendingAmount;

        if ($excludeId) {
            $existingEntry = Expenditure::find($excludeId);
            if ($existingEntry && $existingEntry->spending_type === 'UP') {
                $currentBalance += (float) $existingEntry->gross_value;
            }
        }

        if ($nominal > $currentBalance) {
            return [
                'isValid' => false,
                'balance' => $currentBalance,
                'message' => 'Saldo UP tidak mencukupi (Saldo: Rp ' . number_format($currentBalance, 0, ',', '.') . ')'
            ];
        }

        return ['isValid' => true];
    }

    /**
     * Store a new Expenditure record.
     */
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = auth()->id() ?? 1; // Fallback for testing
            $data['tax'] = $data['tax'] ?? 0;
            $data['net_value'] = (float) $data['gross_value'] - (float) $data['tax'];

            $year = Carbon::parse($data['spending_date'])->year;
            $data['siklus_up'] = $data['siklus_up'] ?? ((in_array($data['spending_type'], ['UP', 'GU'])) ? $this->siklusService->getActiveSiklus($year) : 0);
            $data['nomor_dalam_siklus'] = \App\Models\DocumentSequence::nextNumber('BUKTI_CYCLE', $year, $data['siklus_up'], $data['spending_type']);

            if (in_array($data['spending_type'], ['UP', 'GU'])) {
                $check = $this->checkLedgerBalance($data['spending_date'], $data['gross_value']);
                if (!$check['isValid']) {
                    throw new \Exception($check['message']);
                }
            }

            $expenditure = Expenditure::create($data);

            // Generate and lock global proof number in one go
            $bukti = $this->numberingService->generateNoBukti(
                $expenditure->spending_type,
                $expenditure->spending_date,
                $expenditure->nomor_dalam_siklus,
                $expenditure->siklus_up
            );

            $expenditure->update([
                'no_bukti' => $bukti['no_bukti'],
                'no_bukti_urut' => $bukti['no_bukti_urut'],
                'number_locked_at' => now()
            ]);

            // Record to ledger as credit for all types (including LS)
            $this->ledgerService->recordEntry(
                $expenditure->spending_date,
                'BELANJA_' . $expenditure->spending_type,
                $expenditure->gross_value,
                'expenditures',
                $expenditure->id,
                'CREDIT',
                "{$expenditure->no_bukti} - {$expenditure->description}"
            );

            // LS expenditure impact to bank is now handled by SP2D (DisbursementService CAIR)
            // We remove any existing bank entry to ensure no duplicates from old logic
            app(\App\Services\BankLedgerService::class)->removeEntry('expenditures', $expenditure->id);

            ActivityLog::log(
                'CREATE',
                'EXPENDITURE',
                "Menambah pengeluaran: {$expenditure->description}",
                $expenditure->id,
                null,
                $expenditure->toArray()
            );

            return $expenditure;
        });
    }

    /**
     * Update an existing Expenditure record.
     */
    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $expenditure = Expenditure::findOrFail($id);
            $oldValues = $expenditure->toArray();

            $data['tax'] = $data['tax'] ?? 0;
            $data['net_value'] = (float) $data['gross_value'] - (float) $data['tax'];

            if (isset($data['spending_type']) && in_array($data['spending_type'], ['UP', 'GU'])) {
                $check = $this->checkLedgerBalance($data['spending_date'] ?? $expenditure->spending_date, $data['gross_value'], $id);
                if (!$check['isValid']) {
                    throw new \Exception($check['message']);
                }
            }

            $expenditure->update($data);

            // Update ledger
            $this->ledgerService->recordEntry(
                $expenditure->spending_date,
                'BELANJA_' . $expenditure->spending_type,
                $expenditure->gross_value,
                'expenditures',
                $expenditure->id,
                'CREDIT',
                "{$expenditure->no_bukti} - {$expenditure->description}"
            );

            // LS expenditure impact to bank is now handled by SP2D (DisbursementService CAIR)
            app(\App\Services\BankLedgerService::class)->removeEntry('expenditures', $expenditure->id);

            ActivityLog::log(
                'UPDATE',
                'EXPENDITURE',
                "Mengubah pengeluaran: {$expenditure->description}",
                $expenditure->id,
                $oldValues,
                $expenditure->toArray()
            );

            return $expenditure;
        });
    }

    /**
     * Delete an Expenditure record.
     */
    public function delete($id)
    {
        return DB::transaction(function () use ($id) {
            $expenditure = Expenditure::findOrFail($id);

            // AUDIT SAFETY: Prevent deleting expenditures already linked to an SPJ
            if ($expenditure->spjItems()->exists()) {
                throw new \Exception("Tidak bisa menghapus belanja yang sudah masuk dalam SPJ.");
            }

            $oldValues = $expenditure->toArray();
            $description = $expenditure->description;

            // Remove ledger entry if it exists
            $this->ledgerService->removeEntry('expenditures', $expenditure->id);
            app(\App\Services\BankLedgerService::class)->removeEntry('expenditures', $expenditure->id);

            $expenditure->delete();

            ActivityLog::log(
                'DELETE',
                'EXPENDITURE',
                "Menghapus pengeluaran: {$description}",
                $id,
                $oldValues,
                null
            );

            return true;
        });
    }
}
