<?php

namespace App\Services;

use App\Models\TreasurerCash;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CashLedgerService
{
    /**
     * Record a transaction into the treasurer cash ledger.
     */
    public function recordEntry($date, $type, $amount, $refTable, $refId, $direction = 'DEBIT', $description = null)
    {
        return DB::transaction(function () use ($date, $type, $amount, $refTable, $refId, $direction, $description) {
            $year = Carbon::parse($date)->year;

            // RACE CONDITION PREVENTION: Lock the ledger for this year during update
            // We use a shared semaphore-like lock in the database
            DB::table('treasurer_cash')->whereYear('date', $year)->lockForUpdate()->count();

            // Delete existing entry for this reference if it exists (for updates)
            TreasurerCash::where('ref_table', $refTable)
                ->where('ref_id', $refId)
                ->where('type', $type)
                ->delete();

            $entry = new TreasurerCash();
            $entry->date = $date;
            $entry->type = $type;
            $entry->ref_table = $refTable;
            $entry->ref_id = $refId;
            $entry->description = $description;

            if ($direction === 'DEBIT') {
                $entry->setAttribute('debit', $amount);
                $entry->setAttribute('credit', 0);
            } else {
                $entry->setAttribute('debit', 0);
                $entry->setAttribute('credit', $amount);
            }

            $entry->save();

            $this->rebuildBalances($year);

            return $entry;
        });
    }

    /**
     * Remove an entry from the ledger.
     */
    public function removeEntry($refTable, $refId, $type = null)
    {
        DB::transaction(function () use ($refTable, $refId, $type) {
            $query = TreasurerCash::where('ref_table', $refTable)
                ->where('ref_id', $refId);

            if ($type) {
                $query->where('type', $type);
            }

            $entries = $query->lockForUpdate()->get();

            if ($entries->count() > 0) {
                $year = Carbon::parse($entries->first()->date)->year;
                // Lock the whole year to prevent race during rebuild
                DB::table('treasurer_cash')->whereYear('date', $year)->lockForUpdate()->count();

                foreach ($entries as $entry) {
                    $entry->delete();
                }

                $this->rebuildBalances($year);
            }
        });
    }

    /**
     * Rebuild balances for a specific year to ensure consistency.
     */
    public function rebuildBalances($year)
    {
        /** @var \App\Models\TreasurerCash[] $entries */
        $entries = TreasurerCash::whereYear('date', $year)
            ->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $runningBalance = 0;
        foreach ($entries as $entry) {
            $runningBalance += (float) $entry->debit;
            $runningBalance -= (float) $entry->credit;

            $entry->setAttribute('balance', $runningBalance);
            $entry->saveQuietly();
        }
    }

    /**
     * Get the current running balance.
     */
    public function getCurrentBalance($year, $lock = false)
    {
        $query = TreasurerCash::whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->value('balance') ?? 0;
    }

    /**
     * Force sync the entire ledger from source tables for a given year.
     * This ensures the BKU mathematically matches the accounting rules.
     */
    public function syncLedger($year)
    {
        DB::transaction(function () use ($year) {
            // 1. Clear existing for this year
            TreasurerCash::whereYear('date', $year)->delete();

            // Clear all bank entries associated with system-managed transactions to avoid orphans
            \App\Models\BankAccountLedger::whereYear('date', $year)
                ->whereIn('ref_table', ['fund_disbursements', 'expenditures'])
                ->delete();

            $bankService = app(\App\Services\BankLedgerService::class);

            // Pre-calculate consolidation totals for bank entries (by SP2D/SPM/SPP Number)
            $consolidationTotals = DB::table('fund_disbursements')
                ->whereYear('sp2d_date', $year)
                ->select(DB::raw('COALESCE(sp2d_no, spm_no, spp_no) as ref_no'), DB::raw('SUM(value) as total'))
                ->groupBy('ref_no')
                ->pluck('total', 'ref_no');

            // 2. DISBURSTMENTS (Inflows & Bank Moves)
            $disbursements = \App\Models\FundDisbursement::whereYear('sp2d_date', $year)
                ->whereIn('status', ['SPP', 'SPM', 'CAIR'])
                ->get();

            foreach ($disbursements as $d) {
                $date = $d->sp2d_date;
                $refNo = $d->sp2d_no ?: ($d->spm_no ?: $d->spp_no);
                $totalVal = $consolidationTotals[$refNo] ?? $d->value;

                if ($d->type === 'LS') {
                    // LS always records DEBIT in BKU (Receipt)
                    $this->createEntry($date, 'LS_IN', $d->value, 'fund_disbursements', $d->id, ($d->uraian ?: $d->description));

                    // Bank: Only CREDIT (money goes out to vendor)
                    $bankService->recordEntry($date, 'LS_BANK_OUT', $totalVal, 'fund_disbursements', $d->id, 'CREDIT', "Pembayaran LS ke Vendor ({$refNo})", $refNo);
                } elseif (!$d->expenditure_id && !$d->kode_rekening_id) {
                    // This is a REFILL (UP/GU) - Inflow to Treasurer Cash
                    $this->createEntry($date, "AJU_{$d->type}", $d->value, 'fund_disbursements', $d->id, ($d->uraian ?: ($d->description ?: "Isi Saldo Kas {$d->type}")));

                    // Bank: Only CREDIT (money withdrawn from bank to treasurer cash)
                    $bankService->recordEntry($date, "WITHDRAW_{$d->type}", $totalVal, 'fund_disbursements', $d->id, 'CREDIT', "Penarikan Tunai Kas Bendahara ({$refNo})", $refNo);
                } else {
                    // This is an activity SPP (Outflow check). 
                    $this->createEntry($date, "TRACE_ACTIVITY_{$d->type}", 0, 'fund_disbursements', $d->id, "[Audit Trace] " . ($d->uraian ?: $d->description));
                }
            }

            // 3. EXPENDITURES (The actual Bill/Money Out)
            $expenditures = \App\Models\Expenditure::whereYear('spending_date', $year)
                ->whereIn('spending_type', ['UP', 'GU', 'LS'])
                ->get();

            foreach ($expenditures as $e) {
                // All expenditures reduce the Treasurer Cash liquidity
                $this->createEntry($e->spending_date, "BELANJA_{$e->spending_type}", $e->gross_value, 'expenditures', $e->id, "{$e->no_bukti} - {$e->description}", 'CREDIT');
            }

            // 4. MANUAL DEPOSITS (Direct to Bank)
            $deposits = \App\Models\BankAccountLedger::whereYear('date', $year)
                ->where('type', 'DEPOSIT_MANUAL')
                ->get();

            foreach ($deposits as $dep) {
                $this->createEntry($dep->date, $dep->type, $dep->debit, 'bank_account_ledgers', $dep->id, $dep->description ?: "Setoran Manual");
            }

            // 5. Rebuild final running balances
            $this->rebuildBalances($year);
        });
    }

    /**
     * Helper to create a BKU entry without business logic overhead
     */
    private function createEntry($date, $type, $amount, $refTable, $refId, $description, $direction = 'DEBIT')
    {
        $entry = new TreasurerCash();
        $entry->date = $date;
        $entry->type = $type;
        $entry->ref_table = $refTable;
        $entry->ref_id = $refId;
        $entry->description = $description;

        if ($direction === 'DEBIT') {
            $entry->setAttribute('debit', $amount);
            $entry->setAttribute('credit', 0);
        } else {
            $entry->setAttribute('debit', 0);
            $entry->setAttribute('credit', $amount);
        }

        $entry->save();
        return $entry;
    }
}
