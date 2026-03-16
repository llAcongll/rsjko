<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReportService;
use App\Services\BalanceSheetService;
use App\Services\CashFlowService;
use App\Services\OperationalReportService;
use App\Services\EquityChangeService;
use App\Services\FinancialNotesService;
use App\Services\BudgetBalanceService;
use App\Services\BudgetPlanService;
use App\Services\BusinessBudgetService;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class FinancialStatementController extends Controller
{
    protected $service;
    protected $balanceSheetService;
    protected $cashFlowService;
    protected $operationalReportService;
    protected $equityChangeService;
    protected $financialNotesService;
    protected $budgetBalanceService;
    protected $budgetPlanService;
    protected $businessBudgetService;

    public function __construct(
        ReportService $service,
        BalanceSheetService $balanceSheetService,
        CashFlowService $cashFlowService,
        OperationalReportService $operationalReportService,
        EquityChangeService $equityChangeService,
        FinancialNotesService $financialNotesService,
        BudgetBalanceService $budgetBalanceService,
        BudgetPlanService $budgetPlanService,
        BusinessBudgetService $businessBudgetService
    ) {
        $this->service = $service;
        $this->balanceSheetService = $balanceSheetService;
        $this->cashFlowService = $cashFlowService;
        $this->operationalReportService = $operationalReportService;
        $this->equityChangeService = $equityChangeService;
        $this->financialNotesService = $financialNotesService;
        $this->budgetBalanceService = $budgetBalanceService;
        $this->budgetPlanService = $budgetPlanService;
        $this->businessBudgetService = $businessBudgetService;
    }

    public function getDpa(Request $request)
    {
        $this->authorizePermission('LAP_DPA_VIEW');
        $tahun = session('tahun_anggaran');
        return response()->json(['data' => $this->service->getDpaData($tahun), 'tahun' => $tahun]);
    }

    public function getAnggaran(Request $request)
    {
        $this->authorizePermission('LAP_LRA_VIEW');
        $start = $request->start;
        $end = $request->end;
        $tahun = $request->filled('tahun') ? $request->get('tahun') : session('tahun_anggaran', date('Y'));
        $kategori = $request->get('kategori', 'SEMUA');
        $klasifikasi = $request->get('klasifikasi', 3);

        if (!$start || !$end || $start === 'undefined' || $end === 'undefined') {
            return response()->json(['error' => 'Tanggal mulai dan tanggal akhir wajib diisi'], 422);
        }

        $res = $this->service->getAnggaranData($kategori, $start, $end, $tahun, $klasifikasi);
        $res['category'] = $kategori;
        return response()->json($res);
    }

    public function getNeraca(Request $request)
    {
        $this->authorizePermission('LAP_NERACA_VIEW');
        $bulan = $request->get('bulan', date('n'));
        $tahun = session('tahun_anggaran', date('Y'));
        return response()->json($this->balanceSheetService->getNeracaData($bulan, $tahun));
    }

    public function getLak(Request $request)
    {
        $this->authorizePermission('LAP_LAK_VIEW');
        $tahun = session('tahun_anggaran', date('Y'));
        $start = $request->get('start');
        $end = $request->get('end');
        $periode = $request->get('periode');
        $bulan = $request->get('bulan');

        if ($periode === 'Bulanan' && $bulan) {
            $start = Carbon::create($tahun, $bulan, 1)->startOfMonth()->toDateString();
            $end = Carbon::create($tahun, $bulan, 1)->endOfMonth()->toDateString();
        } elseif ($periode === 'Triwulan' && $request->has('triwulan')) {
            $tw = $request->get('triwulan');
            $startMonth = (($tw - 1) * 3) + 1;
            $start = Carbon::create($tahun, $startMonth, 1)->startOfMonth()->toDateString();
            $end = Carbon::create($tahun, $startMonth + 2, 1)->endOfMonth()->toDateString();
        } elseif ($periode === 'Semester' && $request->has('semester')) {
            $sem = $request->get('semester');
            $startMonth = (($sem - 1) * 6) + 1;
            $start = Carbon::create($tahun, $startMonth, 1)->startOfMonth()->toDateString();
            $end = Carbon::create($tahun, $startMonth + 5, 1)->endOfMonth()->toDateString();
        } elseif ($periode === 'Tahunan') {
            $start = Carbon::create($tahun, 1, 1)->startOfMonth()->toDateString();
            $end = Carbon::create($tahun, 12, 31)->endOfMonth()->toDateString();
        }

        if (!$start) $start = $tahun . '-01-01';
        if (!$end) $end = Carbon::now()->toDateString();

        return response()->json($this->cashFlowService->getLakData($start, $end, $tahun));
    }

    public function getLo(Request $request)
    {
        $this->authorizePermission('LAP_LO_VIEW');
        $bulan = $request->get('bulan', date('n'));
        $tahun = session('tahun_anggaran', date('Y'));
        return response()->json($this->operationalReportService->getLoData($bulan, $tahun));
    }

    public function getLpe(Request $request)
    {
        $this->authorizePermission('LAP_LPE_VIEW');
        $bulan = $request->get('bulan', date('n'));
        $tahun = session('tahun_anggaran', date('Y'));
        return response()->json($this->equityChangeService->getLpeData($bulan, $tahun));
    }

    public function getCalk(Request $request)
    {
        $this->authorizePermission('LAP_CALK_VIEW');
        $tahun = session('tahun_anggaran', date('Y'));
        return response()->json($this->financialNotesService->getCalkData($tahun));
    }

    public function getLpsal(Request $request)
    {
        $this->authorizePermission('LAP_LPSAL_VIEW');
        $tahun = session('tahun_anggaran', date('Y'));
        return response()->json($this->budgetBalanceService->getLpsalData($tahun));
    }

    public function getRka(Request $request)
    {
        $this->authorizePermission('LAP_RKA_VIEW');
        $tahun = session('tahun_anggaran');
        return response()->json($this->budgetPlanService->getRkaData($tahun));
    }

    public function getRba(Request $request)
    {
        $this->authorizePermission('LAP_RBA_VIEW');
        $tahun = session('tahun_anggaran');
        return response()->json($this->businessBudgetService->getRbaData($tahun));
    }
}
