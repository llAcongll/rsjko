<?php

namespace App\Services;

use App\Models\NeracaOpeningBalance;
use App\Models\NeracaManualInput;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EquityChangeService
{
    protected $loService;

    public function __construct(OperationalReportService $loService)
    {
        $this->loService = $loService;
    }

    public function getLpeData($bulan, $tahun)
    {
        $startDate = Carbon::create($tahun, 1, 1)->startOfYear()->toDateString();
        $endDateObj = Carbon::create($tahun, $bulan, 1)->endOfMonth();
        $endDate = $endDateObj->toDateString();
        $formattedEndDate = $endDateObj->locale('id')->translatedFormat('d F Y');

        // 1. Ekuitas Awal
        $ekuitasAwal = NeracaOpeningBalance::where('tahun', $tahun)
            ->where('kelompok', 'EKUITAS')
            ->sum('nominal');

        // 2. Surplus / Defisit LO
        $loData = $this->loService->getLoData($startDate, $endDate, $tahun);
        $surplusDefisitLo = $loData['surplus_defisit'];

        // 3. Koreksi Ekuitas (Adjustments)
        $koreksi = NeracaManualInput::where('tahun', $tahun)
            ->where('bulan', '<=', $bulan)
            ->where('account_key', 'KOREKSI_EKUITAS')
            ->sum('nominal');

        // 4. Ekuitas Akhir
        $ekuitasAkhir = $ekuitasAwal + $surplusDefisitLo + $koreksi;

        return [
            'period' => [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'end_date_formatted' => $formattedEndDate,
            ],
            'ekuitas_awal' => $ekuitasAwal,
            'surplus_defisit_lo' => $surplusDefisitLo,
            'koreksi' => $koreksi,
            'ekuitas_akhir' => $ekuitasAkhir
        ];
    }
}





