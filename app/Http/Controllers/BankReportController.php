<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReportService;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class BankReportController extends Controller
{
    protected $service;

    public function __construct(ReportService $service)
    {
        $this->service = $service;
    }

    public function getRekon(Request $request)
    {
        $this->authorizePermission('LAP_REKON_VIEW');
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

        $data = $this->service->getRekonData($start, $end, $tahun);
        return response()->json($data);
    }

    public function getBku(Request $request)
    {
        $this->authorizePermission('BKU_PENDAPATAN_VIEW');
        $month = $request->get('month');
        $year = $request->get('year', session('tahun_anggaran', date('Y')));

        $res = $this->service->getBkuData($year, $month);
        return response()->json($res);
    }
    
    public function exportRekon(Request $request)
    {
        $this->authorizePermission('LAP_REKON_EXPORT');
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
        $this->authorizePermission('LAP_REKON_EXPORT');
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
}
