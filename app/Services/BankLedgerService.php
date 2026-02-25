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
    public function recordEntry($date, $type, $amount, $refTable, $refId, $direction = 'DEBIT', $description = null)
    {
        return DB::transaction(function () use ($date, $type, $amount, $refTable, $refId, $direction, $description) {
            $year = Carbon::parse($date)->year;

            // Lock ledger for year
            DB::table('bank_account_ledgers')->whereYear('date', $year)->lockForUpdate()->count();

            BankAccountLedger::where('ref_table', $refTable)
                ->where('ref_id', $refId)
                ->delete();

            $entry = new BankAccountLedger();
            $entry->date = $date;
            $entry->type = $type;
            $entry->ref_table = $refTable;
            $entry->ref_id = $refId;
            $entry->description = $description;

            if ($direction === 'DEBIT') { // Uang Masuk
                $entry->debit = $amount;
                $entry->credit = 0;
            } else { // Uang Keluar (Kredit)
                $entry->debit = 0;
                $entry->credit = $amount;
            }

            $entry->save();
            $this->rebuildBalances($year);

            return $entry;
        });
    }

    public function removeEntry($refTable, $refId)
    {
        DB::transaction(function () use ($refTable, $refId) {
            $entry = BankAccountLedger::where('ref_table', $refTable)
                ->where('ref_id', $refId)
                ->lockForUpdate()
                ->first();

            if ($entry) {
                $year = Carbon::parse($entry->date)->year;
                DB::table('bank_account_ledgers')->whereYear('date', $year)->lockForUpdate()->count();
                $entry->delete();
                $this->rebuildBalances($year);
            }
        });
    }

    public function rebuildBalances($year)
    {
        DB::transaction(function () use ($year) {
            $entries = BankAccountLedger::whereYear('date', $year)
                ->orderBy('date', 'asc')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            $runningBalance = 0;

            // Getting balance from previous year if any
            $previousBalance = BankAccountLedger::whereYear('date', '<', $year)
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

    public function getCurrentBalance()
    {
        $latest = BankAccountLedger::orderBy('date', 'desc')->orderBy('id', 'desc')->first();
        return $latest ? (float) $latest->balance : 0;
    }
}
