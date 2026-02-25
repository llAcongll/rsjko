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
    public function removeEntry($refTable, $refId)
    {
        DB::transaction(function () use ($refTable, $refId) {
            $entry = TreasurerCash::where('ref_table', $refTable)
                ->where('ref_id', $refId)
                ->lockForUpdate()
                ->first();

            if ($entry) {
                $year = Carbon::parse($entry->date)->year;
                // Lock the whole year to prevent race during rebuild
                DB::table('treasurer_cash')->whereYear('date', $year)->lockForUpdate()->count();
                $entry->delete();
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
            // BKU Balance Logic (Total Liquidity = Bank + Cash):
            // 1. DEPOSIT_MANUAL: Increases total liquidity.
            // 2. BELANJA_UP, BELANJA_GU, BELANJA_LS: Decreases total liquidity.
            // 3. TERIMA_UP, GU, LS_RECEIPT (SP2D Receipts): 
            //    - UP/GU are transfers from Bank to Cash (Total Liquidity unchanged).
            //    - LS_RECEIPT is an accounting entry (bypasses total liquidity pool or stays neutral).

            if ($entry->type === 'LS_RECEIPT') {
                // Traditional LS refill/accounting entry is neutral to BKU
            } elseif (in_array($entry->type, ['ACTIVITY_UP', 'ACTIVITY_GU'])) {
                // Activity realizations for UP/GU formally reduce the fund balance in BKU
                $runningBalance -= (float) ($entry->debit > 0 ? $entry->debit : $entry->credit);
            } elseif ($entry->type === 'ACTIVITY_LS') {
                // LS activities are neutral to BKU total balance (Direct bank impact)
            } elseif ($entry->type === 'BELANJA_LS') {
                // LS Expenditure reduces total liquidity (Bank reduction at Realisasi stage)
                $runningBalance -= (float) $entry->credit;
            } elseif (str_contains($entry->type, 'BELANJA')) {
                // All other expenditures (UP, GU) reduce the total liquidity (BKU Balance)
                $runningBalance -= (float) $entry->credit;
            } elseif ($entry->type === 'DEPOSIT_MANUAL') {
                $runningBalance += (float) $entry->debit;
            } else {
                // Receipts like TERIMA_UP, GU (Refills) are neutral to TOTAL balance (Bank to Cash transfer)
            }

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
     * This is useful for fixing data after migrations or manual database changes.
     */
    public function syncLedger($year)
    {
        DB::transaction(function () use ($year) {
            // 1. Clear existing for this year
            TreasurerCash::whereYear('date', $year)->delete();

            // 2. Add from Fund Disbursements (UP, GU, LS)
            $disbursements = \App\Models\FundDisbursement::whereYear('sp2d_date', $year)
                ->where('status', 'CAIR')
                ->whereIn('type', ['UP', 'GU', 'LS'])
                ->get();

            foreach ($disbursements as $d) {
                $entry = new TreasurerCash();
                $entry->date = $d->sp2d_date;

                $isActivity = !empty($d->spp_no);
                $typeLabel = $d->type;

                if ($d->type === 'UP') {
                    $typeLabel = $isActivity ? 'ACTIVITY_UP' : 'TERIMA_UP';
                } elseif ($d->type === 'GU') {
                    $typeLabel = $isActivity ? 'ACTIVITY_GU' : 'GU';
                } elseif ($d->type === 'LS') {
                    $typeLabel = $isActivity ? 'ACTIVITY_LS' : 'LS_RECEIPT';
                }

                $entry->type = $typeLabel;
                $entry->ref_table = 'fund_disbursements';
                $entry->ref_id = $d->id;
                $entry->description = $d->description ?: "Penerimaan {$d->type} - {$d->sp2d_no}";
                $entry->setAttribute('debit', $d->value);
                $entry->setAttribute('credit', 0);
                $entry->save();
            }

            // 3. Add from Expenditures (UP, GU, LS)
            $expenditures = \App\Models\Expenditure::whereYear('spending_date', $year)
                ->whereIn('spending_type', ['UP', 'GU', 'LS'])
                ->get();

            foreach ($expenditures as $e) {
                $entry = new TreasurerCash();
                $entry->date = $e->spending_date;
                $entry->type = 'BELANJA_' . $e->spending_type;
                $entry->ref_table = 'expenditures';
                $entry->ref_id = $e->id;
                $entry->description = "{$e->no_bukti} - {$e->description}";
                $entry->setAttribute('debit', 0);
                $entry->setAttribute('credit', $e->gross_value);
                $entry->save();
            }

            // 4. Add from Bank Account Ledger (Manual Deposits)
            $deposits = \App\Models\BankAccountLedger::whereYear('date', $year)
                ->where('type', 'DEPOSIT_MANUAL')
                ->get();

            foreach ($deposits as $dep) {
                $entry = new TreasurerCash();
                $entry->date = $dep->date;
                $entry->type = $dep->type;
                $entry->ref_table = 'bank_account_ledgers';
                $entry->ref_id = $dep->id;
                $entry->description = $dep->description ?: "Setoran Manual";
                $entry->setAttribute('debit', $dep->debit);
                $entry->setAttribute('credit', 0);
                $entry->save();
            }

            // 5. Rebuild balances
            $this->rebuildBalances($year);
        });
    }
}
