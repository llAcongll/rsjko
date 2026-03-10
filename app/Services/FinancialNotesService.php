<?php

namespace App\Services;

use App\Models\CalkSection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialNotesService
{
    protected $lraService;
    protected $loService;
    protected $neracaService;
    protected $lakService;
    protected $lpeService;

    public function __construct(
        ReportService $lraService,
        OperationalReportService $loService,
        BalanceSheetService $neracaService,
        CashFlowService $lakService,
        EquityChangeService $lpeService
    ) {
        $this->lraService = $lraService;
        $this->loService = $loService;
        $this->neracaService = $neracaService;
        $this->lakService = $lakService;
        $this->lpeService = $lpeService;
    }

    public function getCalkData($bulan, $tahun)
    {
        $startDateObj = Carbon::create($tahun, 1, 1)->startOfYear();
        $endDateObj = Carbon::create($tahun, $bulan, 1)->endOfMonth();

        $start = $startDateObj->toDateString();
        $end = $endDateObj->toDateString();

        // 1. Fetch Narrative Sections from calk_sections
        $sections = CalkSection::where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->get()
            ->keyBy('bab');

        // 2. Fetch Data from other services
        $lraData = $this->lraService->getAnggaranData('SEMUA', $start, $end, $tahun);
        $loData = $this->loService->getLoData($start, $end, $tahun);
        $neracaData = $this->neracaService->getNeracaData($bulan, $tahun);
        $lakData = $this->lakService->getLakData($start, $end, $tahun);
        $lpeData = $this->lpeService->getLpeData($bulan, $tahun);

        return [
            'period' => [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'end_date_formatted' => $endDateObj->locale('id')->translatedFormat('d F Y'),
            ],
            'sections' => [
                'BAB_I' => $sections->get('BAB_I')->content ?? '',
                'BAB_II' => $sections->get('BAB_II')->content ?? '',
                'BAB_III' => $sections->get('BAB_III')->content ?? '',
                'BAB_IV' => $sections->get('BAB_IV')->content ?? '',
                'BAB_V' => $sections->get('BAB_V')->content ?? '',
                'BAB_VI' => $sections->get('BAB_VI')->content ?? '',
                'BAB_VII' => $sections->get('BAB_VII')->content ?? '',
            ],
            'reports' => [
                'lra' => $lraData,
                'lo' => $loData,
                'neraca' => $neracaData,
                'lak' => $lakData,
                'lpe' => $lpeData,
            ]
        ];
    }

    public function saveSection($tahun, $bulan, $bab, $content)
    {
        return CalkSection::updateOrCreate(
            ['tahun' => $tahun, 'bulan' => $bulan, 'bab' => $bab],
            ['content' => $content]
        );
    }
}





