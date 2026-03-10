<?php

namespace App\Services;

use App\Models\BankAccountLedger;
use App\Models\TreasurerCash;
use App\Models\ArusKasMapping;
use App\Models\Expenditure;
use App\Models\RevenueMaster;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CashFlowService
{
    public function getLakData($start, $end, $tahun = null)
    {
        if (!$tahun) {
            $tahun = session('tahun_anggaran', date('Y'));
        }

        // 1. Calculate Opening Balance (Combined)
        $openingBank = $this->getBankBalanceAt($start, $tahun);
        $openingCash = $this->getCashBalanceAt($start, $tahun);
        $saldoAwal = $openingBank + $openingCash;

        // 2. Get External Transactions in Period
        // We focus on Expenditures and Revenues which are the "External" flows.
        // Internal moves (WITHDRAW, DEPOSIT) cancel each other out in the combined pool.

        // A. Expenditures (Cash Out)
        $outflows = DB::table('expenditures as e')
            ->leftJoin('arus_kas_mappings as m', 'e.kode_rekening_id', '=', 'm.kode_rekening_id')
            ->leftJoin('kode_rekening as kr', 'e.kode_rekening_id', '=', 'kr.id')
            ->whereBetween('e.spending_date', [$start, $end])
            ->whereYear('e.spending_date', $tahun)
            ->select(
                'm.tipe as arus_kas_type',
                'kr.nama as uraian',
                'kr.kode',
                DB::raw('SUM(IFNULL(e.gross_value, 0)) as total')
            )
            ->groupBy('m.tipe', 'kr.nama', 'kr.kode')
            ->get();

        // B. Revenues (Cash In)
        // Currently revenue_masters has total_all but no direct link to kode_rekening in the master record.
        // Usually, these are all OPERASI.
        $inflows = DB::table('revenue_masters as rm')
            ->whereBetween('rm.tanggal_rk', [$start, $end])
            ->where('rm.tahun', $tahun)
            ->where('rm.is_posted', 1)
            ->select(
                'rm.kategori as uraian',
                DB::raw("CAST('OPERASI' AS CHAR(20)) as arus_kas_type"),
                DB::raw('SUM(IFNULL(rm.total_all, 0)) as total')
            )
            ->groupBy('rm.kategori')
            ->get();

        // 3. Structured Data
        $categories = [
            'OPERASI' => ['in' => [], 'out' => [], 'total_in' => 0, 'total_out' => 0],
            'INVESTASI' => ['in' => [], 'out' => [], 'total_in' => 0, 'total_out' => 0],
            'PENDANAAN' => ['in' => [], 'out' => [], 'total_in' => 0, 'total_out' => 0],
            'UNMAPPED' => ['in' => [], 'out' => [], 'total_in' => 0, 'total_out' => 0],
        ];

        foreach ($inflows as $in) {
            $cat = $in->arus_kas_type ?: 'OPERASI';
            $categories[$cat]['in'][] = $in;
            $categories[$cat]['total_in'] += $in->total;
        }

        foreach ($outflows as $out) {
            $cat = $out->arus_kas_type ?: 'UNMAPPED';
            $categories[$cat]['out'][] = $out;
            $categories[$cat]['total_out'] += $out->total;
        }

        $totalIn = array_sum(array_column($categories, 'total_in'));
        $totalOut = array_sum(array_column($categories, 'total_out'));
        $kenaikan = $totalIn - $totalOut;
        $saldoAkhir = $saldoAwal + $kenaikan;

        return [
            'period' => [
                'start' => $start,
                'end' => $end,
                'tahun' => $tahun,
                'start_formatted' => Carbon::parse($start)->locale('id')->translatedFormat('d F Y'),
                'end_formatted' => Carbon::parse($end)->locale('id')->translatedFormat('d F Y'),
            ],
            'saldo_awal' => $saldoAwal,
            'categories' => $categories,
            'total_masuk' => $totalIn,
            'total_keluar' => $totalOut,
            'kenaikan' => $kenaikan,
            'saldo_akhir' => $saldoAkhir
        ];
    }

    private function getBankBalanceAt($date, $tahun)
    {
        if (!$date)
            return 0;

        $sum = DB::table('bank_account_ledgers')
            ->whereYear('date', $tahun)
            ->where('date', '<', $date)
            ->select(DB::raw('SUM(IFNULL(debit, 0)) - SUM(IFNULL(credit, 0)) as balance'))
            ->first();
        return (float) ($sum->balance ?? 0);
    }

    private function getCashBalanceAt($date, $tahun)
    {
        if (!$date)
            return 0;

        // Sum from Bku Pengeluaran (Treasurer Cash)
        $expenditureCash = DB::table('treasurer_cash')
            ->whereYear('date', $tahun)
            ->where('date', '<', $date)
            ->select(DB::raw('SUM(IFNULL(debit, 0)) - SUM(IFNULL(credit, 0)) as balance'))
            ->first();

        // Sum from Bku Penerimaan (Income Cash Book)
        $incomeCash = DB::table('bku_penerimaan')
            ->whereYear('tanggal', $tahun)
            ->where('tanggal', '<', $date)
            ->select(DB::raw('SUM(IFNULL(penerimaan, 0)) - SUM(IFNULL(pengeluaran, 0)) as balance'))
            ->first();

        return (float) (($expenditureCash->balance ?? 0) + ($incomeCash->balance ?? 0));
    }
}





