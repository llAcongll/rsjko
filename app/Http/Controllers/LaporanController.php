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

    public function __construct(\App\Services\ReportService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_PENDAPATAN') || auth()->user()->hasPermission('LAPORAN_VIEW'), 403);
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

        return response()->json([
            'range' => ['start' => $start, 'end' => $end],
            'summary' => $res['summary'],
            'totals' => $res['totals'],
            'rooms' => $roomData['flat_total'],
            'room_patients' => $roomData['flat_count'],
            'patients' => array_combine(array_keys($res['summary']), array_column($res['summary'], 'count')),
            'breakdown' => $breakdown
        ]);
    }

    public function export(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT') || auth()->user()->hasPermission('LAPORAN_VIEW'), 403);
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
        ]);
    }

    public function exportPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT_PDF') || auth()->user()->hasPermission('LAPORAN_VIEW'), 403);
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

        $pdf = Pdf::loadView('dashboard.exports.pendapatan_pdf', [
            'start' => $start,
            'end' => $end,
            'summary' => $res['summary'],
            'breakdown' => $breakdown,
            'rooms' => $roomData['stats'],
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]);

        return $pdf->download("Laporan_Pendapatan_{$start}_to_{$end}.pdf");
    }



    public function getRekon(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_REKON') || auth()->user()->hasPermission('LAPORAN_VIEW'), 403);
        $tahun = session('tahun_anggaran', date('Y'));
        $data = $this->service->getRekonData($tahun);
        return response()->json($data);
    }

    public function getPiutang(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_PIUTANG') || auth()->user()->hasPermission('LAPORAN_VIEW'), 403);
        $start = $request->get('start');
        $end = $request->get('end');
        $tahun = $request->get('tahun', session('tahun_anggaran', date('Y')));

        $res = $this->service->getPiutangData($start, $end, $tahun);
        return response()->json(['data' => $res['data'], 'totals' => $res['totals'], 'tahun' => $tahun]);
    }

    public function getMou(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_MOU') || auth()->user()->hasPermission('LAPORAN_VIEW'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');

        $final = $this->service->getMouData($start, $end, $tahun);
        return response()->json($final);
    }

    public function getAnggaran(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_ANGGARAN') || auth()->user()->hasPermission('LAPORAN_VIEW'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');
        $category = $request->get('category', 'SEMUA');

        $res = $this->service->getAnggaranData($category, $start, $end, $tahun);
        $res['category'] = $category;
        return response()->json($res);
    }

    public function getPengeluaran(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_PENGELUARAN') || auth()->user()->hasPermission('LAPORAN_VIEW'), 403);
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
        abort_unless(auth()->user()->hasPermission('LAPORAN_ANGGARAN') || auth()->user()->hasPermission('LAPORAN_VIEW'), 403);
        $tahun = session('tahun_anggaran');
        $data = $this->service->getDpaData($tahun);

        return response()->json(['data' => $data, 'tahun' => $tahun]);
    }

    // Export Methods
    public function exportRekon(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran', date('Y'));
        $data = $this->service->getRekonData($tahun);

        return view('dashboard.exports.rekon', [
            'data' => $data,
            'start' => $start,
            'end' => $end,
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]);
    }

    public function exportRekonPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT_PDF'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran', date('Y'));
        $data = $this->service->getRekonData($tahun);

        $pdf = Pdf::loadView('dashboard.exports.rekon_pdf', [
            'data' => $data,
            'start' => $start,
            'end' => $end,
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]);
        return $pdf->download("Laporan_Rekon_{$start}_to_{$end}.pdf");
    }

    public function exportPiutang(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT'), 403);
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
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT_PDF'), 403);
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
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT'), 403);
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
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT_PDF'), 403);
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
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');
        $category = $request->get('category', 'PENDAPATAN');
        $res = $this->service->getAnggaranData($category, $start, $end, $tahun);

        return view('dashboard.exports.anggaran', array_merge($res, [
            'start' => $start,
            'end' => $end,
            'category' => $category,
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]));
    }

    public function exportAnggaranPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT_PDF'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');
        $category = $request->get('category', 'PENDAPATAN');
        $res = $this->service->getAnggaranData($category, $start, $end, $tahun);

        $pdf = Pdf::loadView('dashboard.exports.anggaran_pdf', array_merge($res, [
            'start' => $start,
            'end' => $end,
            'category' => $category,
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]));
        return $pdf->download("Laporan_Realisasi_Anggaran_{$start}_to_{$end}.pdf");
    }

    public function exportPengeluaran(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT'), 403);
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
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT_PDF'), 403);
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
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT'), 403);
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
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT_PDF'), 403);
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
}
