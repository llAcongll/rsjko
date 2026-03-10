<?php

namespace App\Services;

use App\Models\RevenueMaster;
use App\Models\PenyesuaianPendapatan;
use App\Models\Expenditure;
use App\Models\LoMapping;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OperationalReportService
{
    public function getLoData($start, $end, $tahun)
    {
        $startDate = Carbon::parse($start)->startOfDay();
        $endDate = Carbon::parse($end)->endOfDay();

        // 1. PENDAPATAN LO
        $revenue = $this->aggregateRevenue($startDate, $endDate, $tahun);

        // 2. BEBAN OPERASIONAL
        $expenses = $this->aggregateExpenses($startDate, $endDate, $tahun);

        // 3. SURPLUS / DEFISIT
        $totalRevenue = $revenue['total'];
        $totalExpenses = $expenses['total'];
        $surplusDefisit = $totalRevenue - $totalExpenses;

        return [
            'period' => [
                'start' => $start,
                'end' => $end,
                'tahun' => $tahun,
                'start_formatted' => $startDate->locale('id')->translatedFormat('d F Y'),
                'end_formatted' => $endDate->locale('id')->translatedFormat('d F Y'),
            ],
            'revenue' => $revenue,
            'expenses' => $expenses,
            'surplus_defisit' => $surplusDefisit
        ];
    }

    private function aggregateRevenue($start, $end, $tahun)
    {
        // Get from revenue_masters
        $masters = DB::table('revenue_masters')
            ->whereBetween('tanggal', [$start, $end])
            ->select('kategori', DB::raw('SUM(total_all) as total'))
            ->groupBy('kategori')
            ->get();

        $data = [
            'UMUM' => 0,
            'BPJS' => 0,
            'JAMINAN' => 0,
            'KERJASAMA' => 0,
            'LAIN' => 0,
        ];

        foreach ($masters as $m) {
            if (isset($data[$m->kategori])) {
                $data[$m->kategori] = (float) $m->total;
            }
        }

        // Apply adjustments (Penyesuaian Pendapatan)
        // Adjustments usually reduce the revenue (pots/admin bank)
        $adjustments = DB::table('penyesuaian_pendapatans')
            ->whereBetween('tanggal', [$start, $end])
            ->select('kategori', DB::raw('SUM(potongan + administrasi_bank) as total_adj'))
            ->groupBy('kategori')
            ->get();

        foreach ($adjustments as $adj) {
            if (isset($data[$adj->kategori])) {
                $data[$adj->kategori] -= (float) $adj->total_adj;
            }
        }

        return [
            'items' => [
                ['label' => 'Pendapatan Layanan Umum', 'value' => $data['UMUM']],
                ['label' => 'Pendapatan Layanan BPJS', 'value' => $data['BPJS']],
                ['label' => 'Pendapatan Layanan Jaminan', 'value' => $data['JAMINAN']],
                ['label' => 'Pendapatan Kerjasama', 'value' => $data['KERJASAMA']],
                ['label' => 'Pendapatan Lain-lain LO', 'value' => $data['LAIN']],
            ],
            'total' => array_sum($data)
        ];
    }

    private function aggregateExpenses($start, $end, $tahun)
    {
        // Get from expenditures joined with lo_mappings
        $expenses = DB::table('expenditures')
            ->leftJoin('lo_mappings', 'expenditures.kode_rekening_id', '=', 'lo_mappings.kode_rekening_id')
            ->whereBetween('spending_date', [$start, $end])
            ->select(
                DB::raw('COALESCE(lo_mappings.kategori, "UNMAPPED") as kategori'),
                DB::raw('SUM(gross_value) as total')
            )
            ->groupBy('kategori')
            ->get();

        $categories = [
            'BEBAN_PEGAWAI' => ['label' => 'Beban Pegawai', 'value' => 0],
            'BEBAN_BARANG_JASA' => ['label' => 'Beban Barang dan Jasa', 'value' => 0],
            'BEBAN_PENYUSUTAN' => ['label' => 'Beban Penyusutan dan Amortisasi', 'value' => 0],
            'BEBAN_TRANSFER' => ['label' => 'Beban Transfer', 'value' => 0],
            'BEBAN_LAINNYA' => ['label' => 'Beban Operasional Lainnya', 'value' => 0],
            'UNMAPPED' => ['label' => 'Beban Belum Terklasifikasi', 'value' => 0],
        ];

        foreach ($expenses as $e) {
            if (isset($categories[$e->kategori])) {
                $categories[$e->kategori]['value'] = (float) $e->total;
            } else {
                $categories['UNMAPPED']['value'] += (float) $e->total;
            }
        }

        return [
            'items' => array_values($categories),
            'total' => array_sum(array_column($categories, 'value'))
        ];
    }
}





