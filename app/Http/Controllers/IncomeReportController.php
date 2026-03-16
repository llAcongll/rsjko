<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReportService;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PendapatanExport;
use Barryvdh\DomPDF\Facade\Pdf;

class IncomeReportController extends Controller
{
    protected $service;

    public function __construct(ReportService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->authorizePermission('LAP_PENDAPATAN_VIEW');
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');

        $res = $this->service->getRevenueSummary($start, $end, $tahun);
        $roomData = $this->service->getRoomStatsWithDeductions($start, $end, $tahun);

        $categories = $this->getRevenueCategories();
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
        $this->authorizePermission('LAP_PENDAPATAN_EXPORT');
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');

        $res = $this->service->getRevenueSummary($start, $end, $tahun);
        $roomData = $this->service->getRoomStatsWithDeductions($start, $end, $tahun);

        $categories = $this->getRevenueCategories();
        $breakdown = [];
        foreach ($categories as $key => $meta) {
            $breakdown[$key] = array_merge($meta, $this->service->getDetailedBreakdown($key, $tahun, $start, $end));
        }

        $resDetailed = $this->service->getDetailedRevenueByType($start, $end, $tahun);

        return Excel::download(
            new PendapatanExport([
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
            ]),
            "Laporan_Pendapatan_{$start}_to_{$end}.xlsx"
        );
    }

    public function exportPdf(Request $request)
    {
        $this->authorizePermission('LAP_PENDAPATAN_EXPORT');
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');

        $res = $this->service->getRevenueSummary($start, $end, $tahun);
        $roomData = $this->service->getRoomStatsWithDeductions($start, $end, $tahun);

        $categories = $this->getRevenueCategories();
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

    public function getPiutang(Request $request)
    {
        $this->authorizePermission('LAP_PIUTANG_VIEW');
        $start = $request->get('start');
        $end = $request->get('end');
        $tahun = $request->get('tahun', session('tahun_anggaran', date('Y')));

        $res = $this->service->getPiutangData($start, $end, $tahun);
        return response()->json(['data' => $res['data'], 'totals' => $res['totals'], 'tahun' => $tahun]);
    }

    public function getMou(Request $request)
    {
        $this->authorizePermission('LAP_MOU_VIEW');
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');

        $final = $this->service->getMouData($start, $end, $tahun);
        return response()->json($final);
    }

    private function getRevenueCategories()
    {
        return [
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
    }
}
