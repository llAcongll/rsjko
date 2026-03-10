<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\KodeRekening;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    protected $service;
    protected $cashFlowService;
    protected $balanceSheetService;
    protected $operationalReportService;
    protected $equityChangeService;
    protected $financialNotesService;
    protected $budgetBalanceService;
    protected $budgetPlanService;
    protected $businessBudgetService;

    public function __construct(
        \App\Services\ReportService $service,
        \App\Services\CashFlowService $cashFlowService,
        \App\Services\BalanceSheetService $balanceSheetService,
        \App\Services\OperationalReportService $operationalReportService,
        \App\Services\EquityChangeService $equityChangeService,
        \App\Services\FinancialNotesService $financialNotesService,
        \App\Services\BudgetBalanceService $budgetBalanceService,
        \App\Services\BudgetPlanService $budgetPlanService,
        \App\Services\BusinessBudgetService $businessBudgetService
    ) {
        $this->service = $service;
        $this->cashFlowService = $cashFlowService;
        $this->balanceSheetService = $balanceSheetService;
        $this->operationalReportService = $operationalReportService;
        $this->equityChangeService = $equityChangeService;
        $this->financialNotesService = $financialNotesService;
        $this->budgetBalanceService = $budgetBalanceService;
        $this->budgetPlanService = $budgetPlanService;
        $this->businessBudgetService = $businessBudgetService;
    }

    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_PENDAPATAN_VIEW'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');

        $res = $this->service->getRevenueSummary($start, $end, $tahun);
        $roomData = $this->service->getRoomStatsWithDeductions($start, $end, $tahun);

        $categories = [
            'PASIEN_UMUM' => ['kode' => '4.1.02.01.001.00005', 'nama' => 'Retribusi Pelayanan Kesehatan Pasien Non Jaminan (Mandiri)'],
            'BPJS_JAMINAN' => ['kode' => '4.1.02.01.001.00005', 'nama' => 'Retribusi Pelayanan Kesehatan Pasien Jaminan'],
            'KERJASAMA' => ['kode' => '4.1.02.02.001.00005', 'nama' => 'Retribusi Pemakaian Ruangan'],
            'PKL' => ['kode' => '4.1.04.16.004.00001', 'nama' => 'Pendapatan BLUD dari Hasil Kerja Sama dengan Pihak Praktek Kerja Lapangan (PKL)'],
            'MAGANG' => ['kode' => '4.1.04.16.004.00001', 'nama' => 'Pendapatan BLUD dari Hasil Kerja Sama dengan Pihak Praktek Magang'],
            'LAIN_LAIN' => ['kode' => '4.1.04.16.004.00006', 'nama' => 'Pendapatan BLUD dari Lain-lain Pendapatan BLUD yang Sah Tanpa Kerja Sama'],
            'PENELITIAN' => ['kode' => '4.1.04.16.004.00006', 'nama' => 'Pendapatan BLUD dari Pengembangan Usaha Penelitian'],
            'PERMINTAAN_DATA' => ['kode' => '4.1.04.16.004.00006', 'nama' => 'Pendapatan BLUD dari Pengembangan Usaha Permintaan Data'],
            'STUDY_BANDING' => ['kode' => '4.1.04.16.004.00006', 'nama' => 'Pendapatan BLUD dari Pengembangan Usaha Study Banding'],
        ];

        $breakdown = [];
        foreach ($categories as $key => $meta) {
            $breakdown[$key] = array_merge($meta, $this->service->getDetailedBreakdown($key, $tahun, $start, $end));
        }

        $resDetailed = $this->service->getDetailedRevenueByType($start, $end, $tahun);

        return response()->json([
            'range' => ['start' => $start, 'end' => $end],
            'summary' => $res['summary'],
            'totals' => $res['totals'],
            'rooms' => $roomData['flat_total'],
            'room_patients' => $roomData['flat_count'],
            'patients' => array_combine(array_keys($res['summary']), array_column($res['summary'], 'count')),
            'breakdown' => $breakdown,
            'additive_report' => $resDetailed
        ]);
    }

    public function export(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_PENDAPATAN_EXPORT'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');

        $res = $this->service->getRevenueSummary($start, $end, $tahun);
        $roomData = $this->service->getRoomStatsWithDeductions($start, $end, $tahun);

        $categories = [
            'BPJS_JAMINAN' => ['kode' => '4.1.02.01.001.00005', 'nama' => 'Retribusi Pelayanan Kesehatan Pasien Jaminan'],
            'PASIEN_UMUM' => ['kode' => '4.1.02.01.001.00005', 'nama' => 'Retribusi Pelayanan Kesehatan Pasien Non Jaminan (Mandiri)'],
            'KERJASAMA' => ['kode' => '4.1.02.02.001.00005', 'nama' => 'Retribusi Pemakaian Ruangan'],
            'PKL' => ['kode' => '4.1.04.16.004.00001', 'nama' => 'Pendapatan BLUD dari Hasil Kerja Sama dengan Pihak Praktek Kerja Lapangan (PKL)'],
            'MAGANG' => ['kode' => '4.1.04.16.004.00001', 'nama' => 'Pendapatan BLUD dari Hasil Kerja Sama dengan Pihak Praktek Magang'],
            'LAIN_LAIN' => ['kode' => '4.1.04.16.004.00006', 'nama' => 'Pendapatan BLUD dari Lain-lain Pendapatan BLUD yang Sah Tanpa Kerja Sama'],
            'PENELITIAN' => ['kode' => '4.1.04.16.004.00006', 'nama' => 'Pendapatan BLUD dari Pengembangan Usaha Penelitian'],
            'PERMINTAAN_DATA' => ['kode' => '4.1.04.16.004.00006', 'nama' => 'Pendapatan BLUD dari Pengembangan Usaha Permintaan Data'],
            'STUDY_BANDING' => ['kode' => '4.1.04.16.004.00006', 'nama' => 'Pendapatan BLUD dari Pengembangan Usaha Study Banding'],
        ];

        $breakdown = [];
        foreach ($categories as $key => $meta) {
            $breakdown[$key] = array_merge($meta, $this->service->getDetailedBreakdown($key, $tahun, $start, $end));
        }

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Laporan_Pendapatan_{$start}_to_{$end}.xls\"");

        $resDetailed = $this->service->getDetailedRevenueByType($start, $end, $tahun);

        return view('dashboard.exports.pendapatan', [
            'start' => $start,
            'end' => $end,
            'summary' => $res['summary'],
            'breakdown' => $breakdown,
            'rooms' => $roomData['stats'],
            'tahun' => $tahun,
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
            'additive_report' => $resDetailed
        ]);
    }

    public function exportPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_PENDAPATAN_EXPORT'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');

        $res = $this->service->getRevenueSummary($start, $end, $tahun);
        $roomData = $this->service->getRoomStatsWithDeductions($start, $end, $tahun);

        $categories = [
            'BPJS_JAMINAN' => ['kode' => '4.1.02.01.001.00005', 'nama' => 'Retribusi Pelayanan Kesehatan Pasien Jaminan'],
            'PASIEN_UMUM' => ['kode' => '4.1.02.01.001.00005', 'nama' => 'Retribusi Pelayanan Kesehatan Pasien Non Jaminan (Mandiri)'],
            'KERJASAMA' => ['kode' => '4.1.02.02.001.00005', 'nama' => 'Retribusi Pemakaian Ruangan'],
            'PKL' => ['kode' => '4.1.04.16.004.00001', 'nama' => 'Pendapatan BLUD dari Hasil Kerja Sama dengan Pihak Praktek Kerja Lapangan (PKL)'],
            'MAGANG' => ['kode' => '4.1.04.16.004.00001', 'nama' => 'Pendapatan BLUD dari Hasil Kerja Sama dengan Pihak Praktek Magang'],
            'LAIN_LAIN' => ['kode' => '4.1.04.16.004.00006', 'nama' => 'Pendapatan BLUD dari Lain-lain Pendapatan BLUD yang Sah Tanpa Kerja Sama'],
            'PENELITIAN' => ['kode' => '4.1.04.16.004.00006', 'nama' => 'Pendapatan BLUD dari Pengembangan Usaha Penelitian'],
            'PERMINTAAN_DATA' => ['kode' => '4.1.04.16.004.00006', 'nama' => 'Pendapatan BLUD dari Pengembangan Usaha Permintaan Data'],
            'STUDY_BANDING' => ['kode' => '4.1.04.16.004.00006', 'nama' => 'Pendapatan BLUD dari Pengembangan Usaha Study Banding'],
        ];

        $breakdown = [];
        foreach ($categories as $key => $meta) {
            $breakdown[$key] = array_merge($meta, $this->service->getDetailedBreakdown($key, $tahun, $start, $end));
        }

        $resDetailed = $this->service->getDetailedRevenueByType($start, $end, $tahun);

        $pdf = Pdf::loadView('dashboard.exports.pendapatan_pdf', [
            'start' => $start,
            'end' => $end,
            'summary' => $res['summary'],
            'breakdown' => $breakdown,
            'rooms' => $roomData['stats'],
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
            'additive_report' => $resDetailed
        ]);

        return $pdf->download("Laporan_Pendapatan_{$start}_to_{$end}.pdf");
    }



    public function getRekon(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_REKON_VIEW'), 403);
        $tahun = session('tahun_anggaran', date('Y'));

        $start = $request->get('start');
        $end = $request->get('end');
        $periode = $request->get('periode'); // Bulanan, Triwulan, Semester, Tahunan
        $bulan = $request->get('bulan'); // 1-12

        if ($periode === 'Bulanan' && $bulan) {
            $start = Carbon::create($tahun, $bulan, 1)->startOfMonth()->toDateString();
            $end = Carbon::create($tahun, $bulan, 1)->endOfMonth()->toDateString();
        } elseif ($periode === 'Triwulan' && $request->has('triwulan')) {
            $tw = $request->get('triwulan'); // 1, 2, 3, 4
            $startMonth = (($tw - 1) * 3) + 1;
            $start = Carbon::create($tahun, $startMonth, 1)->startOfMonth()->toDateString();
            $end = Carbon::create($tahun, $startMonth + 2, 1)->endOfMonth()->toDateString();
        } elseif ($periode === 'Semester' && $request->has('semester')) {
            $sem = $request->get('semester'); // 1, 2
            $startMonth = (($sem - 1) * 6) + 1;
            $start = Carbon::create($tahun, $startMonth, 1)->startOfMonth()->toDateString();
            $end = Carbon::create($tahun, $startMonth + 5, 1)->endOfMonth()->toDateString();
        } elseif ($periode === 'Tahunan') {
            $start = Carbon::create($tahun, 1, 1)->startOfMonth()->toDateString();
            $end = Carbon::create($tahun, 12, 31)->endOfMonth()->toDateString();
        }

        $data = $this->service->getRekonData($start, $end, $tahun);
        return response()->json($data);
    }

    public function getPiutang(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_PIUTANG_VIEW'), 403);
        $start = $request->get('start');
        $end = $request->get('end');
        $tahun = $request->get('tahun', session('tahun_anggaran', date('Y')));

        $res = $this->service->getPiutangData($start, $end, $tahun);
        return response()->json(['data' => $res['data'], 'totals' => $res['totals'], 'tahun' => $tahun]);
    }

    public function getMou(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_MOU_VIEW'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');

        $final = $this->service->getMouData($start, $end, $tahun);
        return response()->json($final);
    }

    public function getAnggaran(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_LRA_VIEW'), 403);

        $start = $request->start;
        $end = $request->end;

        \Log::info('LRA Date Range', [
            'start' => $start,
            'end' => $end
        ]);

        $tahun = $request->get('tahun', session('tahun_anggaran', date('Y')));
        $kategori = $request->get('kategori', 'SEMUA');
        $klasifikasi = $request->get('klasifikasi', 3);

        if (!$start || !$end) {
            return response()->json([
                'error' => 'Tanggal mulai dan tanggal akhir wajib diisi'
            ], 422);
        }

        $res = $this->service->getAnggaranData($kategori, $start, $end, $tahun, $klasifikasi);
        $res['category'] = $kategori;
        return response()->json($res);
    }

    public function pengeluaran(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_PENGELUARAN_VIEW'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');

        $res = $this->service->getPengeluaranSummary($start, $end, $tahun);
        return response()->json([
            'data' => $res['data'],
            'summary' => $res['summary'],
            'period' => ['start' => $start, 'end' => $end]
        ]);
    }

    public function getDpa(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_DPA_VIEW'), 403);
        $tahun = session('tahun_anggaran');
        $data = $this->service->getDpaData($tahun);

        return response()->json(['data' => $data, 'tahun' => $tahun]);
    }

    public function getBku(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('BKU_PENDAPATAN_VIEW'), 403);
        $month = $request->get('month');
        $year = $request->get('year', session('tahun_anggaran', date('Y')));

        $res = $this->service->getBkuData($year, $month);
        return response()->json($res);
    }

    public function getLak(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_LAK_VIEW'), 403);
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

        // Fallback defaults
        if (!$start)
            $start = $tahun . '-01-01';
        if (!$end)
            $end = Carbon::now()->toDateString();

        $res = $this->cashFlowService->getLakData($start, $end, $tahun);
        return response()->json($res);
    }

    public function getNeraca(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_NERACA_VIEW'), 403);
        $bulan = $request->get('bulan', date('n'));
        $tahun = session('tahun_anggaran', date('Y'));

        $res = $this->balanceSheetService->getNeracaData($bulan, $tahun);
        return response()->json($res);
    }

    // Export Methods
    public function exportRekon(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_REKON_EXPORT'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran', date('Y'));
        $res = $this->service->getRekonData($start, $end, $tahun);

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Laporan_Rekon_{$tahun}.xls\"");

        return view('dashboard.exports.rekon', [
            'recap' => $res['recap'],
            'analysis' => $res['analysis'],
            'section_b' => $res['section_b'] ?? [],
            'start' => $start,
            'end' => $end,
            'tahun' => $tahun,
            'label' => $res['period']['label'] ?? 'TAHUNAN',
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]);
    }

    public function exportRekonPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_REKON_EXPORT'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran', date('Y'));
        $res = $this->service->getRekonData($start, $end, $tahun);

        $pdf = Pdf::loadView('dashboard.exports.rekon_pdf', [
            'recap' => $res['recap'],
            'analysis' => $res['analysis'],
            'section_b' => $res['section_b'] ?? [],
            'start' => $start,
            'end' => $end,
            'tahun' => $tahun,
            'label' => $res['period']['label'] ?? 'TAHUNAN',
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]);
        return $pdf->download("Laporan_Rekon_{$tahun}.pdf");
    }

    public function exportPiutang(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_PIUTANG_EXPORT'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = $request->get('tahun', session('tahun_anggaran', date('Y')));
        $res = $this->service->getPiutangData($start, $end, $tahun);

        return view('dashboard.exports.piutang', [
            'data' => $res['data'],
            'totals' => $res['totals'],
            'start' => $start,
            'end' => $end,
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]);
    }

    public function exportPiutangPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_PIUTANG_EXPORT'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = $request->get('tahun', session('tahun_anggaran', date('Y')));
        $res = $this->service->getPiutangData($start, $end, $tahun);

        $pdf = Pdf::loadView('dashboard.exports.piutang_pdf', [
            'data' => $res['data'],
            'totals' => $res['totals'],
            'start' => $start,
            'end' => $end,
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]);
        return $pdf->download("Laporan_Piutang_{$start}_to_{$end}.pdf");
    }

    public function exportMou(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_MOU_EXPORT'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');
        $data = $this->service->getMouData($start, $end, $tahun);

        return view('dashboard.exports.mou', [
            'data' => $data,
            'start' => $start,
            'end' => $end,
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]);
    }

    public function exportMouPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_MOU_EXPORT'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');
        $data = $this->service->getMouData($start, $end, $tahun);

        $pdf = Pdf::loadView('dashboard.exports.mou_pdf', [
            'data' => $data,
            'start' => $start,
            'end' => $end,
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]);
        return $pdf->download("Laporan_MOU_{$start}_to_{$end}.pdf");
    }

    public function exportAnggaran(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_LRA_EXPORT'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');
        $category = $request->get('category', 'PENDAPATAN');
        $res = $this->service->getAnggaranData($category, $start, $end, $tahun);

        return view('dashboard.exports.anggaran', array_merge($res, [
            'start' => $start,
            'end' => $end,
            'category' => $category,
            'report_title' => $request->get('report_title'),
            'report_period' => $request->get('report_period'),
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]));
    }

    public function exportAnggaranPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_LRA_EXPORT'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');
        $category = $request->get('category', 'PENDAPATAN');
        $res = $this->service->getAnggaranData($category, $start, $end, $tahun);

        $pdf = Pdf::loadView('dashboard.exports.anggaran_pdf', array_merge($res, [
            'start' => $start,
            'end' => $end,
            'category' => $category,
            'report_title' => $request->get('report_title'),
            'report_period' => $request->get('report_period'),
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]));
        return $pdf->download("Laporan_Realisasi_Anggaran_{$start}_to_{$end}.pdf");
    }

    public function exportPengeluaran(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_PENGELUARAN_EXPORT'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');
        $res = $this->service->getPengeluaranSummary($start, $end, $tahun);

        return view('dashboard.exports.pengeluaran', [
            'data' => $res['data'],
            'summary' => $res['summary'],
            'start' => $start,
            'end' => $end,
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]);
    }

    public function exportPengeluaranPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_PENGELUARAN_EXPORT'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');
        $res = $this->service->getPengeluaranSummary($start, $end, $tahun);

        $pdf = Pdf::loadView('dashboard.exports.pengeluaran_pdf', [
            'data' => $res['data'],
            'summary' => $res['summary'],
            'start' => $start,
            'end' => $end,
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]);
        return $pdf->download("Laporan_Pengeluaran_{$start}_to_{$end}.pdf");
    }

    public function exportDpa(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_DPA_EXPORT'), 403);
        $tahun = session('tahun_anggaran');
        $data = $this->service->getDpaData($tahun);

        return view('dashboard.exports.dpa', [
            'data' => $data,
            'tahun' => $tahun,
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]);
    }

    public function exportDpaPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_DPA_EXPORT'), 403);
        $tahun = session('tahun_anggaran');
        $data = $this->service->getDpaData($tahun);

        $pdf = Pdf::loadView('dashboard.exports.dpa_pdf', [
            'data' => $data,
            'tahun' => $tahun,
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]);
        return $pdf->download("Laporan_DPA_{$tahun}.pdf");
    }

    public function exportBku(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('BKU_PENDAPATAN_EXPORT'), 403);
        $month = $request->get('month');
        $year = $request->get('year', session('tahun_anggaran', date('Y')));
        $res = $this->service->getBkuData($year, $month);

        return view('dashboard.exports.bku', [
            'data' => $res['data'],
            'summary' => $res['summary'],
            'opening_balance' => $res['opening_balance'],
            'period' => $res['period'],
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]);
    }

    public function exportBkuPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('BKU_PENDAPATAN_EXPORT'), 403);
        $month = $request->get('month');
        $year = $request->get('year', session('tahun_anggaran', date('Y')));
        $res = $this->service->getBkuData($year, $month);

        $pdf = Pdf::loadView('dashboard.exports.bku_pdf', [
            'data' => $res['data'],
            'summary' => $res['summary'],
            'opening_balance' => $res['opening_balance'],
            'period' => $res['period'],
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]);

        return $pdf->download("Buku_Kas_Umum_{$res['period']}.pdf");
    }

    public function exportLak(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_LAK_EXPORT'), 403);
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

        // Fallback defaults
        if (!$start)
            $start = $tahun . '-01-01';
        if (!$end)
            $end = Carbon::now()->toDateString();

        $res = $this->cashFlowService->getLakData($start, $end, $tahun);

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"LAK_{$tahun}.xls\"");

        return view('dashboard.exports.lak', array_merge($res, [
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]));
    }

    public function exportLakPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_LAK_EXPORT'), 403);
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

        // Fallback defaults
        if (!$start)
            $start = $tahun . '-01-01';
        if (!$end)
            $end = Carbon::now()->toDateString();

        $res = $this->cashFlowService->getLakData($start, $end, $tahun);

        $pdf = Pdf::loadView('dashboard.exports.lak_pdf', array_merge($res, [
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]));
        return $pdf->download("Laporan_Arus_Kas_{$tahun}.pdf");
    }

    public function getNeracaManualInputs(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_NERACA_MANUAL'), 403);
        $bulan = $request->get('bulan', date('n'));
        $tahun = session('tahun_anggaran', date('Y'));

        $opening = \App\Models\NeracaOpeningBalance::where('tahun', $tahun)->get();
        $manual = \App\Models\NeracaManualInput::where('tahun', $tahun)->where('bulan', $bulan)->get();

        return response()->json([
            'opening' => $opening,
            'manual' => $manual
        ]);
    }

    public function saveNeracaManualInputs(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_NERACA_MANUAL'), 403);
        $bulan = $request->get('bulan', date('n'));
        $tahun = session('tahun_anggaran', date('Y'));
        $data = $request->get('data', []);

        foreach ($data as $item) {
            if (isset($item['type']) && $item['type'] === 'opening') {
                \App\Models\NeracaOpeningBalance::updateOrCreate(
                    ['tahun' => $tahun, 'kelompok' => $item['kelompok'], 'sub_kelompok' => $item['sub_kelompok']],
                    ['nominal' => $item['nominal']]
                );
            } else {
                \App\Models\NeracaManualInput::updateOrCreate(
                    ['tahun' => $tahun, 'bulan' => $bulan, 'account_key' => $item['account_key']],
                    ['nominal' => $item['nominal'], 'keterangan' => $item['keterangan'] ?? '']
                );
            }
        }

        return response()->json(['success' => true]);
    }

    public function exportNeraca(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_NERACA_EXPORT'), 403);
        $bulan = $request->get('bulan', date('n'));
        $tahun = session('tahun_anggaran', date('Y'));
        $res = $this->balanceSheetService->getNeracaData($bulan, $tahun);

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Neraca_{$tahun}_{$bulan}.xls\"");

        return view('dashboard.exports.neraca', array_merge($res, [
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]));
    }

    public function exportNeracaPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_NERACA_EXPORT'), 403);
        $bulan = $request->get('bulan', date('n'));
        $tahun = session('tahun_anggaran', date('Y'));
        $res = $this->balanceSheetService->getNeracaData($bulan, $tahun);

        $pdf = Pdf::loadView('dashboard.exports.neraca_pdf', array_merge($res, [
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]));
        return $pdf->download("Neraca_{$tahun}_{$bulan}.pdf");
    }

    public function getLo(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_LO_VIEW'), 403);
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

        // Fallback defaults
        if (!$start)
            $start = $tahun . '-01-01';
        if (!$end)
            $end = Carbon::now()->toDateString();

        $res = $this->operationalReportService->getLoData($start, $end, $tahun);
        return response()->json($res);
    }

    public function exportLo(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_LO_EXPORT'), 403);
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

        if (!$start)
            $start = $tahun . '-01-01';
        if (!$end)
            $end = Carbon::now()->toDateString();

        $res = $this->operationalReportService->getLoData($start, $end, $tahun);

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"LO_{$tahun}.xls\"");

        return view('dashboard.exports.lo', array_merge($res, [
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]));
    }

    public function exportLoPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_LO_EXPORT'), 403);
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

        if (!$start)
            $start = $tahun . '-01-01';
        if (!$end)
            $end = Carbon::now()->toDateString();

        $res = $this->operationalReportService->getLoData($start, $end, $tahun);

        $pdf = Pdf::loadView('dashboard.exports.lo_pdf', array_merge($res, [
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]));
        return $pdf->download("Laporan_Operasional_{$tahun}.pdf");
    }

    public function getLpe(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_NERACA_VIEW'), 403);
        $tahun = session('tahun_anggaran', date('Y'));
        $bulan = $request->get('bulan', date('n'));
        $periode = $request->get('periode');

        // Handle various period types for consistency with other reports
        if ($periode === 'Bulanan' && $request->has('bulan')) {
            $bulan = $request->get('bulan');
        } elseif ($periode === 'Triwulan' && $request->has('triwulan')) {
            $bulan = $request->get('triwulan') * 3;
        } elseif ($periode === 'Semester' && $request->has('semester')) {
            $bulan = $request->get('semester') * 6;
        } elseif ($periode === 'Tahunan') {
            $bulan = 12;
        }

        $res = $this->equityChangeService->getLpeData($bulan, $tahun);
        return response()->json($res);
    }

    public function exportLpe(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_LPE_EXPORT'), 403);
        $tahun = session('tahun_anggaran', date('Y'));
        $bulan = $request->get('bulan', 12);

        $data = $this->equityChangeService->getLpeData($bulan, $tahun);

        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        $filename = 'LAPORAN_PERUBAHAN_EKUITAS_' . $tahun . '_' . $bulan . '.xls';

        return response()->view('dashboard.exports.lpe', array_merge($data, [
            'ptKiri' => $ptKiri,
            'ptTengah' => $ptTengah,
            'ptKanan' => $ptKanan
        ]))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportLpePdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_LPE_EXPORT'), 403);
        $tahun = session('tahun_anggaran', date('Y'));
        $bulan = $request->get('bulan', 12);

        $data = $this->equityChangeService->getLpeData($bulan, $tahun);

        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        $pdf = Pdf::loadView('dashboard.exports.lpe_pdf', array_merge($data, [
            'ptKiri' => $ptKiri,
            'ptTengah' => $ptTengah,
            'ptKanan' => $ptKanan
        ]))->setPaper('a4', 'portrait');

        return $pdf->download('LAPORAN_PERUBAHAN_EKUITAS_' . $tahun . '_' . $bulan . '.pdf');
    }
    public function getCalk(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_CALK_VIEW') || auth()->user()->isAdmin(), 403);
        $tahun = session('tahun_anggaran', date('Y'));
        $bulan = $request->get('bulan', 12);

        $res = $this->financialNotesService->getCalkData($bulan, $tahun);
        return response()->json($res);
    }

    public function saveCalk(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_CALK_MANUAL') || auth()->user()->isAdmin(), 403);
        $tahun = $request->get('tahun', session('tahun_anggaran', date('Y')));
        $bulan = $request->get('bulan');
        $bab = $request->get('bab');
        $content = $request->get('content');

        $res = $this->financialNotesService->saveSection($tahun, $bulan, $bab, $content);
        return response()->json(['success' => true, 'data' => $res]);
    }

    public function exportCalk(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_CALK_EXPORT'), 403);
        $tahun = session('tahun_anggaran', date('Y'));
        $bulan = $request->get('bulan', 12);

        $data = $this->financialNotesService->getCalkData($bulan, $tahun);

        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        $filename = 'CaLK_' . $tahun . '_' . $bulan . '.xls';

        return response()->view('dashboard.exports.calk', array_merge($data, [
            'ptKiri' => $ptKiri,
            'ptTengah' => $ptTengah,
            'ptKanan' => $ptKanan
        ]))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportCalkPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_CALK_EXPORT'), 403);
        $tahun = session('tahun_anggaran', date('Y'));
        $bulan = $request->get('bulan', 12);

        $data = $this->financialNotesService->getCalkData($bulan, $tahun);

        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        $pdf = Pdf::loadView('dashboard.exports.calk_pdf', array_merge($data, [
            'ptKiri' => $ptKiri,
            'ptTengah' => $ptTengah,
            'ptKanan' => $ptKanan
        ]))->setPaper('a4', 'portrait');

        return $pdf->download('CaLK_' . $tahun . '_' . $bulan . '.pdf');
    }

    public function getLpsal(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_NERACA_VIEW'), 403);
        $tahun = session('tahun_anggaran', date('Y'));
        $bulan = $request->get('bulan', date('n'));
        $periode = $request->get('periode');

        if ($periode === 'Bulanan' && $request->has('bulan')) {
            $bulan = $request->get('bulan');
        } elseif ($periode === 'Triwulan' && $request->has('triwulan')) {
            $bulan = $request->get('triwulan') * 3;
        } elseif ($periode === 'Semester' && $request->has('semester')) {
            $bulan = $request->get('semester') * 6;
        } elseif ($periode === 'Tahunan') {
            $bulan = 12;
        }

        $res = $this->budgetBalanceService->getLpsalData($bulan, $tahun);
        return response()->json($res);
    }

    public function exportLpsal(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_LPSAL_EXPORT'), 403);
        $tahun = session('tahun_anggaran', date('Y'));
        $bulan = $request->get('bulan', 12);

        $data = $this->budgetBalanceService->getLpsalData($bulan, $tahun);

        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        $filename = 'LAPORAN_PERUBAHAN_SAL_' . $tahun . '_' . $bulan . '.xls';

        return response()->view('dashboard.exports.lpsal', array_merge($data, [
            'ptKiri' => $ptKiri,
            'ptTengah' => $ptTengah,
            'ptKanan' => $ptKanan
        ]))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportLpsalPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_LPSAL_EXPORT'), 403);
        $tahun = session('tahun_anggaran', date('Y'));
        $bulan = $request->get('bulan', 12);

        $data = $this->budgetBalanceService->getLpsalData($bulan, $tahun);

        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        $pdf = Pdf::loadView('dashboard.exports.lpsal_pdf', array_merge($data, [
            'ptKiri' => $ptKiri,
            'ptTengah' => $ptTengah,
            'ptKanan' => $ptKanan
        ]))->setPaper('a4', 'portrait');

        return $pdf->download('LAPORAN_PERUBAHAN_SAL_' . $tahun . '_' . $bulan . '.pdf');
    }

    public function getRka(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_RKA_VIEW') || auth()->user()->isAdmin(), 403);
        $tahun = $request->get('tahun', session('tahun_anggaran', date('Y')));
        $res = $this->budgetPlanService->getRkaData($tahun);
        return response()->json($res);
    }

    public function exportRka(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_RKA_EXPORT'), 403);
        $tahun = $request->get('tahun', session('tahun_anggaran', date('Y')));
        $data = $this->budgetPlanService->getRkaData($tahun);

        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        $filename = 'LAPORAN_RKA_' . $tahun . '.xls';

        return response()->view('dashboard.exports.rka', array_merge($data, [
            'ptKiri' => $ptKiri,
            'ptTengah' => $ptTengah,
            'ptKanan' => $ptKanan
        ]))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportRkaPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_RKA_EXPORT'), 403);
        $tahun = $request->get('tahun', session('tahun_anggaran', date('Y')));
        $data = $this->budgetPlanService->getRkaData($tahun);

        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.exports.rka_pdf', array_merge($data, [
            'ptKiri' => $ptKiri,
            'ptTengah' => $ptTengah,
            'ptKanan' => $ptKanan
        ]))->setPaper('a4', 'portrait');

        return $pdf->download('LAPORAN_RKA_' . $tahun . '.pdf');
    }

    public function getRba(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_RBA_VIEW') || auth()->user()->isAdmin(), 403);
        $tahun = $request->get('tahun', session('tahun_anggaran', date('Y')));
        $res = $this->businessBudgetService->getRbaData($tahun);
        return response()->json($res);
    }

    public function exportRba(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_RBA_EXPORT'), 403);
        $tahun = $request->get('tahun', session('tahun_anggaran', date('Y')));
        $data = $this->businessBudgetService->getRbaData($tahun);

        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        $filename = 'LAPORAN_RBA_' . $tahun . '.xls';

        return response()->view('dashboard.exports.rba', array_merge($data, [
            'ptKiri' => $ptKiri,
            'ptTengah' => $ptTengah,
            'ptKanan' => $ptKanan
        ]))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportRbaPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAP_RBA_EXPORT'), 403);
        $tahun = $request->get('tahun', session('tahun_anggaran', date('Y')));
        $data = $this->businessBudgetService->getRbaData($tahun);

        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.exports.rba_pdf', array_merge($data, [
            'ptKiri' => $ptKiri,
            'ptTengah' => $ptTengah,
            'ptKanan' => $ptKanan
        ]))->setPaper('a4', 'portrait');

        return $pdf->download('LAPORAN_RBA_' . $tahun . '.pdf');
    }
}





