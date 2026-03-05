<?php

namespace App\Services;

use App\Models\BankAccountLedger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BankLedgerService
{
    /**
     * Record a transaction into the bank account ledger (Rekening Koran)
     */
    public function recordEntry($date, $type, $amount, $refTable, $refId, $direction = 'DEBIT', $description = null, $refNo = null, $bank = 'BRK')
    {
        return DB::transaction(function () use ($date, $type, $amount, $refTable, $refId, $direction, $description, $refNo, $bank) {
            $year = Carbon::parse($date)->year;

            // Lock ledger for year and bank
            DB::table('bank_account_ledgers')
                ->whereYear('date', $year)
                ->where('bank', $bank)
                ->lockForUpdate()
                ->count();

            // Find existing entry: Check ref_table, ref_id AND type
            $query = BankAccountLedger::where('ref_table', $refTable)
                ->where('type', $type);

            if ($refNo) {
                $query->where('ref_no', $refNo);
            } else {
                $query->where('ref_id', $refId);
            }

            $entry = $query->first();

            if (!$entry) {
                $entry = new BankAccountLedger();
                $entry->ref_id = $refId;
                $entry->ref_table = $refTable;
                $entry->type = $type; // Ensure type is set for new entries
            }

            $entry->date = $date;
            $entry->type = $type;
            $entry->ref_no = $refNo;
            $entry->description = $description;
            $entry->bank = $bank;

            if ($direction === 'DEBIT') { // Uang Masuk
                $entry->debit = $amount;
                $entry->credit = 0;
            } else { // Uang Keluar (Kredit)
                $entry->debit = 0;
                $entry->credit = $amount;
            }

            $entry->save();
            $this->rebuildBalances($year, $bank);

            return $entry;
        });
    }

    public function removeEntry($refTable, $refId, $type = null)
    {
        DB::transaction(function () use ($refTable, $refId, $type) {
            $query = BankAccountLedger::where('ref_table', $refTable)
                ->where('ref_id', $refId);

            if ($type) {
                $query->where('type', $type);
            }

            $entries = $query->lockForUpdate()->get();

            if ($entries->count() > 0) {
                $first = $entries->first();
                $year = Carbon::parse($first->date)->year;
                $bank = $first->bank;

                DB::table('bank_account_ledgers')
                    ->whereYear('date', $year)
                    ->where('bank', $bank)
                    ->lockForUpdate()
                    ->count();

                foreach ($entries as $entry) {
                    $entry->delete();
                }

                $this->rebuildBalances($year, $bank);
            }
        });
    }

    public function rebuildBalances($year, $bank = null)
    {
        if (!$bank) {
            $banks = BankAccountLedger::distinct()->pluck('bank');
            foreach ($banks as $b) {
                $this->rebuildBalances($year, $b);
            }
            return;
        }

        DB::transaction(function () use ($year, $bank) {
            $entries = BankAccountLedger::whereYear('date', $year)
                ->where('bank', $bank)
                ->orderBy('date', 'asc')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            $runningBalance = 0;

            // Getting balance from previous year if any
            $previousBalance = BankAccountLedger::whereYear('date', '<', $year)
                ->where('bank', $bank)
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->value('balance');

            if ($previousBalance) {
                $runningBalance = $previousBalance;
            }

            foreach ($entries as $entry) {
                $runningBalance += $entry->debit;
                $runningBalance -= $entry->credit;

                if ($entry->balance != $runningBalance) {
                    $entry->balance = $runningBalance;
                    $entry->save();
                }
            }
        });
    }

    public function getCurrentBalance($bank = 'BRK')
    {
        $latest = BankAccountLedger::where('bank', $bank)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->first();
        return $latest ? (float) $latest->balance : 0;
    }
}
