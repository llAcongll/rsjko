<?php

namespace App\Services;

use App\Models\NeracaOpeningBalance;
use App\Models\NeracaManualInput;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BudgetBalanceService
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function getLpsalData($bulan, $tahun)
    {
        $startDate = $tahun . '-01-01';
        $endDateObj = Carbon::create($tahun, $bulan, 1)->endOfMonth();
        $endDate = $endDateObj->toDateString();
        $formattedEndDate = $endDateObj->locale('id')->translatedFormat('d F Y');

        // 1. SAL Awal (Opening Balance)
        $salAwal = NeracaOpeningBalance::where('tahun', $tahun)
            ->where('sub_kelompok', 'SAL_AWAL')
            ->sum('nominal');

        // 2. SiLPA Tahun Berjalan (LRA: Pendapatan - Belanja)
        // Using ReportService for consistency with LRA
        $lraData = $this->reportService->getAnggaranData('SEMUA', $startDate, $endDate, $tahun);

        $pendReal = $lraData['sub_totals']['pendapatan']['real'] ?? 0;
        $pengReal = $lraData['sub_totals']['pengeluaran']['real'] ?? 0;
        $silpa = $pendReal - $pengReal;

        // 3. Penggunaan SAL (Typically from Financing, often manual input if not specifically categorized)
        // User mentioned fund_disbursements / pengeluaran, but financing usually separate from standard spending.
        // We look for manual inputs with key 'PENGGUNAAN_SAL'
        $penggunaanSal = NeracaManualInput::where('tahun', $tahun)
            ->where('bulan', '<=', $bulan)
            ->where('account_key', 'PENGGUNAAN_SAL')
            ->sum('nominal');

        // 4. Koreksi SAL (Adjustments)
        $koreksiSal = NeracaManualInput::where('tahun', $tahun)
            ->where('bulan', '<=', $bulan)
            ->where('account_key', 'KOREKSI_SAL')
            ->sum('nominal');

        // 5. SAL Akhir (The Result)
        $salAkhir = $salAwal + $silpa - $penggunaanSal + $koreksiSal;

        return [
            'period' => [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'end_date' => $endDate,
                'end_date_formatted' => $formattedEndDate,
            ],
            'sal_awal' => (float) $salAwal,
            'silpa' => (float) $silpa,
            'penggunaan_sal' => (float) $penggunaanSal,
            'koreksi' => (float) $koreksiSal,
            'sal_akhir' => (float) $salAkhir,
            'components' => [
                'pendapatan_real' => (float) $pendReal,
                'belanja_real' => (float) $pengReal
            ]
        ];
    }
}





