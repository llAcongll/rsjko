<?php

namespace App\Services;

use App\Models\BkuPenerimaan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IncomeCashBookService
{
    /**
     * Record a transaction into the income cash book.
     */
    public function recordEntry($date, $uraian, $penerimaan, $pengeluaran, $sumber, $reference_id)
    {
        return DB::transaction(function () use ($date, $uraian, $penerimaan, $pengeluaran, $sumber, $reference_id) {
            $year = Carbon::parse($date)->year;

            // Delete existing entry for this reference if it exists
            BkuPenerimaan::where('sumber', $sumber)
                ->where('reference_id', $reference_id)
                ->delete();

            $entry = new BkuPenerimaan();
            $entry->tanggal = $date;
            $entry->uraian = $uraian;
            $entry->penerimaan = $penerimaan;
            $entry->pengeluaran = $pengeluaran;
            $entry->sumber = $sumber;
            $entry->reference_id = $reference_id;
            $entry->save();

            $this->rebuildBalances($year);

            return $entry;
        });
    }

    /**
     * Rebuild balances for a specific year.
     */
    public function rebuildBalances($year)
    {
        $entries = BkuPenerimaan::whereYear('tanggal', $year)
            ->orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $runningBalance = 0;
        foreach ($entries as $entry) {
            /** @var \App\Models\BkuPenerimaan $entry */
            $runningBalance += (float) $entry->penerimaan;
            $runningBalance -= (float) $entry->pengeluaran;

            $entry->saldo = number_format($runningBalance, 2, '.', '');
            $entry->save();
        }
    }

    /**
     * Force sync the income ledger from source tables.
     */
    public function syncLedger($year)
    {
        DB::transaction(function () use ($year) {
            // 1. Clear existing for this year
            BkuPenerimaan::whereYear('tanggal', $year)->delete();

            // 2. Fetch Cash Incomes (Summarized by Date and Category)
            $incomeTables = [
                'pendapatan_umum' => 'Umum',
                'pendapatan_bpjs' => 'BPJS',
                'pendapatan_jaminan' => 'Jaminan',
                'pendapatan_kerjasama' => 'Kerjasama',
                'pendapatan_lain' => 'Lain-lain'
            ];

            foreach ($incomeTables as $table => $label) {
                $daily = DB::table($table)
                    ->join('revenue_masters', "{$table}.revenue_master_id", '=', 'revenue_masters.id')
                    ->where("{$table}.tahun", $year)
                    ->where("{$table}.metode_pembayaran", 'TUNAI')
                    ->where('revenue_masters.is_posted', true)
                    ->select("{$table}.tanggal", DB::raw("SUM({$table}.total) as total_harian"))
                    ->groupBy("{$table}.tanggal")
                    ->get();

                foreach ($daily as $row) {
                    BkuPenerimaan::create([
                        'tanggal' => $row->tanggal,
                        'uraian' => "Pendapatan {$label} Tunai",
                        'penerimaan' => (float) $row->total_harian,
                        'pengeluaran' => 0,
                        'sumber' => ($label === 'Umum') ? 'Tunai' : $label,
                        'reference_id' => 0
                    ]);
                }
            }

            // 3. Fetch Deposits to Bank (Summarized by Date)
            // A transaction is a "Setoran Bank" only if it's a Credit in Bank (C) 
            // AND the linked Revenue Master was paid in TUNAI (Cash).
            $dailyDeposits = DB::table('rekening_korans as rk')
                ->join('revenue_masters as rm', 'rk.revenue_master_id', '=', 'rm.id')
                ->where('rm.tahun', $year)
                ->where('rk.cd', 'C')
                ->where('rm.metode_pembayaran', 'TUNAI')
                ->select('rk.tanggal', DB::raw('SUM(rk.jumlah) as total_setoran'))
                ->groupBy('rk.tanggal')
                ->get();

            foreach ($dailyDeposits as $dep) {
                BkuPenerimaan::create([
                    'tanggal' => $dep->tanggal,
                    'uraian' => "Setoran Pendapatan Tunai ke Bank",
                    'penerimaan' => 0,
                    'pengeluaran' => (float) $dep->total_setoran,
                    'sumber' => 'Setoran Bank',
                    'reference_id' => 0
                ]);
            }

            // 4. Rebuild balances
            $this->rebuildBalances($year);
        });
    }

    /**
     * Get ledger data for a specific year and optional month.
     */
    public function getLedgerData($year, $month = null)
    {
        $query = BkuPenerimaan::whereYear('tanggal', $year);

        $openingBalance = 0;
        if ($month) {
            $openingBalance = (float) BkuPenerimaan::whereYear('tanggal', $year)
                ->whereMonth('tanggal', '<', $month)
                ->orderBy('tanggal', 'desc')
                ->orderBy('id', 'desc')
                ->value('saldo') ?? 0;

            $query->whereMonth('tanggal', $month);
        }

        $data = $query->orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Calculate Cumulative Totals up to end of period
        $cumulativeQuery = BkuPenerimaan::whereYear('tanggal', $year);
        if ($month) {
            $cumulativeQuery->whereMonth('tanggal', '<=', $month);
        }
        $cumulativePenerimaan = (float) $cumulativeQuery->sum('penerimaan');
        $cumulativePengeluaran = (float) $cumulativeQuery->sum('pengeluaran');

        // Calculate Bank Balances up to end of period
        $endDate = null;
        if ($month) {
            $endDate = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
        } else {
            $endDate = Carbon::create($year, 12, 31)->toDateString();
        }

        $brkBalance = DB::table('rekening_korans')
            ->where('bank', 'Bank Riau Kepri Syariah')
            ->where('tanggal', '<=', $endDate)
            ->where('tahun', $year)
            ->select(DB::raw('SUM(CASE WHEN cd = "C" THEN jumlah ELSE -jumlah END) as balance'))
            ->value('balance') ?? 0;

        $bsiBalance = DB::table('rekening_korans')
            ->where('bank', 'Bank Syariah Indonesia')
            ->where('tanggal', '<=', $endDate)
            ->where('tahun', $year)
            ->select(DB::raw('SUM(CASE WHEN cd = "C" THEN jumlah ELSE -jumlah END) as balance'))
            ->value('balance') ?? 0;

        $totalPenerimaan = (float) $data->sum('penerimaan');
        $totalPengeluaran = (float) $data->sum('pengeluaran');
        $finalSaldo = (float) ($data->last() ? $data->last()->saldo : $openingBalance);

        return [
            'data' => $data,
            'opening_balance' => $openingBalance,
            'summary' => [
                'total_penerimaan' => $totalPenerimaan,
                'total_pengeluaran' => $totalPengeluaran,
                'cumulative_penerimaan' => $cumulativePenerimaan,
                'cumulative_pengeluaran' => $cumulativePengeluaran,
                'final_saldo' => $finalSaldo,
                'bank_bsi' => (float) $bsiBalance,
                'bank_brk' => (float) $brkBalance
            ]
        ];
    }
}





