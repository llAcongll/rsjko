<?php

namespace App\Services;

use App\Models\NeracaOpeningBalance;
use App\Models\NeracaManualInput;
use App\Models\Piutang;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BalanceSheetService
{
    protected $cashFlowService;
    protected $operationalReportService;
    protected $equityChangeService;

    public function __construct(
        CashFlowService $cashFlowService,
        OperationalReportService $operationalReportService,
        EquityChangeService $equityChangeService
    ) {
        $this->cashFlowService = $cashFlowService;
        $this->operationalReportService = $operationalReportService;
        $this->equityChangeService = $equityChangeService;
    }

    public function getNeracaData($bulan, $tahun)
    {
        $endDateObj = Carbon::create($tahun, $bulan, 1)->endOfMonth();
        $endDate = $endDateObj->toDateString();
        $formattedEndDate = strtoupper($endDateObj->locale('id')->translatedFormat('d F Y'));
        $startDate = Carbon::create($tahun, 1, 1)->startOfYear()->toDateString();

        // 1. KAS (Must match LAK Ending Balance)
        // LAK logic: Opening Balance (start of period) + Net Change in period.
        // For Neraca, it is simply the balance at EndDate.
        $cashData = $this->cashFlowService->getLakData($startDate, $endDate, $tahun);
        $totalKas = $cashData['saldo_akhir'];

        // 2. PIUTANG
        // We sum all Piutang records that are NOT 'LUNAS' by the end of this period
        // and also check if there's an opening balance for piutang from previous years.
        $piutangOpening = NeracaOpeningBalance::where('tahun', $tahun)
            ->where('sub_kelompok', 'PIUTANG')
            ->sum('nominal');

        $piutangCurrent = DB::table('piutangs')
            ->where('tahun', $tahun)
            ->where('tanggal', '<=', $endDate)
            ->where('status', '!=', 'LUNAS')
            ->sum('jumlah_piutang');

        $totalPiutang = $piutangOpening + $piutangCurrent;

        // 3. PERSEDIAAN (Manual)
        $persediaanOpening = NeracaOpeningBalance::where('tahun', $tahun)
            ->where('sub_kelompok', 'PERSEDIAAN')
            ->sum('nominal');

        $persediaanInput = NeracaManualInput::where('tahun', $tahun)
            ->where('bulan', '<=', $bulan)
            ->where('account_key', 'persediaan')
            ->orderBy('bulan', 'desc')
            ->first();

        $totalPersediaan = $persediaanInput ? $persediaanInput->nominal : $persediaanOpening;

        // 4. ASET TETAP (Manual)
        $asetTetapOpening = NeracaOpeningBalance::where('tahun', $tahun)
            ->where('kelompok', 'ASET_TETAP')
            ->sum('nominal');

        $asetTetapAdj = NeracaManualInput::where('tahun', $tahun)
            ->where('bulan', '<=', $bulan)
            ->where('account_key', 'aset_tetap')
            ->sum('nominal');

        $totalAsetTetap = $asetTetapOpening + $asetTetapAdj;

        // 5. KEWAJIBAN (Manual)
        $kewajibanOpening = NeracaOpeningBalance::where('tahun', $tahun)
            ->where('kelompok', 'KEWAJIBAN')
            ->sum('nominal');

        $kewajibanAdj = NeracaManualInput::where('tahun', $tahun)
            ->where('bulan', '<=', $bulan)
            ->where('account_key', 'kewajiban')
            ->sum('nominal');

        $totalKewajiban = $kewajibanOpening + $kewajibanAdj;

        // 6. EKUITAS (Calculated from EquityChangeService for consistency)
        $totalAset = $totalKas + $totalPiutang + $totalPersediaan + $totalAsetTetap;

        // Use EquityChangeService to get the theoretical Equity based on LPE
        $lpeData = $this->equityChangeService->getLpeData($bulan, $tahun);
        $totalEkuitasLpe = $lpeData['ekuitas_akhir'];
        $ekuitasAwal = $lpeData['ekuitas_awal'];
        $koreksi = $lpeData['koreksi'];

        // Integration with LO for detail
        $loData = $this->operationalReportService->getLoData($startDate, $endDate, $tahun);
        $surplusDefisitLo = $loData['surplus_defisit'];

        // In accounting: Assets = Liabilities + Equity
        // If there's a difference between A-L and the LPE calculation, it's an "Unbalanced" value
        // But for the sake of the report requested, we display the LPE value.
        $totalEkuitas = $totalEkuitasLpe;

        return [
            'period' => [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'end_date' => $endDate,
                'end_date_formatted' => $formattedEndDate
            ],
            'assets' => [
                'lancar' => [
                    'kas' => $totalKas,
                    'piutang' => $totalPiutang,
                    'persediaan' => $totalPersediaan,
                    'total' => $totalKas + $totalPiutang + $totalPersediaan
                ],
                'tetap' => [
                    'total' => $totalAsetTetap
                ],
                'grand_total' => $totalAset
            ],
            'liabilities' => [
                'total' => $totalKewajiban
            ],
            'equity' => [
                'total' => $totalEkuitas,
                'surplus_defisit_lo' => $surplusDefisitLo,
                'ekuitas_awal' => $ekuitasAwal,
                'koreksi' => $koreksi
            ]
        ];
    }
}





