<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\KodeRekening;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function getPengeluaran(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_PENGELUARAN') || auth()->user()->hasPermission('LAPORAN_VIEW'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');

        $query = DB::table('pengeluaran')
            ->join('kode_rekening', 'pengeluaran.kode_rekening_id', '=', 'kode_rekening.id')
            ->whereYear('pengeluaran.tanggal', $tahun)
            ->whereBetween('pengeluaran.tanggal', [$start, $end]);

        // Detail per Account (Grouped)
        $details = (clone $query)
            ->select(
                'kode_rekening.kode',
                'kode_rekening.nama',
                DB::raw('SUM(nominal) as total'),
                DB::raw("SUM(CASE WHEN metode_pembayaran = 'UP' THEN nominal ELSE 0 END) as up"),
                DB::raw("SUM(CASE WHEN metode_pembayaran = 'GU' THEN nominal ELSE 0 END) as gu"),
                DB::raw("SUM(CASE WHEN metode_pembayaran = 'LS' THEN nominal ELSE 0 END) as ls")
            )
            ->groupBy('kode_rekening.kode', 'kode_rekening.nama')
            ->orderBy('kode_rekening.kode')
            ->get();

        // Summary per Category
        $summary = DB::table('pengeluaran')
            ->whereYear('tanggal', $tahun)
            ->whereBetween('tanggal', [$start, $end])
            ->select(
                'kategori',
                DB::raw('SUM(nominal) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('kategori')
            ->get()
            ->keyBy('kategori');

        return response()->json([
            'data' => $details,
            'summary' => $summary,
            'period' => [
                'start' => $start,
                'end' => $end
            ]
        ]);
    }

    public function getDpa(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_ANGGARAN') || auth()->user()->hasPermission('LAPORAN_VIEW'), 403);
        $tahun = session('tahun_anggaran');

        $rootNodes = KodeRekening::whereNull('parent_id')
            ->orderBy('kode')
            ->get();

        $flatList = [];
        foreach ($rootNodes as $node) {
            $this->processDpaNode($node, $tahun, $flatList);
        }

        return response()->json([
            'data' => $flatList,
            'tahun' => $tahun
        ]);
    }

    private function processDpaNode($node, $tahun, &$flatList)
    {
        $total = 0;
        $childList = [];

        if ($node->tipe === 'detail') {
            // Get rincian
            $rincian = DB::table('anggaran_rincian')
                ->join('anggaran_rekening', 'anggaran_rincian.anggaran_rekening_id', '=', 'anggaran_rekening.id')
                ->where('anggaran_rekening.kode_rekening_id', $node->id)
                ->where('anggaran_rekening.tahun', $tahun)
                ->select(
                    'anggaran_rincian.uraian',
                    'anggaran_rincian.volume',
                    'anggaran_rincian.satuan',
                    'anggaran_rincian.tarif',
                    'anggaran_rincian.subtotal'
                )
                ->get();

            foreach ($rincian as $r) {
                $total += $r->subtotal;
                $childList[] = (object) [
                    'tipe' => 'rincian',
                    'kode_rekening' => '',
                    'uraian' => $r->uraian,
                    'volume' => $r->volume,
                    'satuan' => $r->satuan,
                    'tarif' => $r->tarif,
                    'subtotal' => $r->subtotal,
                    'level' => $node->level + 1
                ];
            }
        } else {
            foreach ($node->children as $child) {
                $res = $this->processDpaNode($child, $tahun, $childList);
                $total += $res['total'];
            }
        }

        // Header node
        $item = (object) [
            'tipe' => 'header',
            'kode_rekening' => $node->kode,
            'uraian' => $node->nama,
            'volume' => null,
            'satuan' => null,
            'tarif' => 0,
            'subtotal' => $total,
            'level' => $node->level
        ];

        // Only add if there is subtotal
        if ($total > 0) {
            $flatList[] = $item;
            foreach ($childList as $cl) {
                $flatList[] = $cl;
            }
        }

        return ['total' => $total];
    }

    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_PENDAPATAN') || auth()->user()->hasPermission('LAPORAN_VIEW'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());

        $tables = [
            'UMUM' => 'pendapatan_umum',
            'BPJS' => 'pendapatan_bpjs',
            'JAMINAN' => 'pendapatan_jaminan',
            'KERJASAMA' => 'pendapatan_kerjasama',
            'LAIN' => 'pendapatan_lain'
        ];

        $data = [];
        $totalRs = 0;
        $totalPelayanan = 0;
        $totalAll = 0;

        foreach ($tables as $key => $table) {
            $stats = DB::table($table)
                ->whereBetween('tanggal', [$start, $end])
                ->where('tahun', session('tahun_anggaran'))
                ->selectRaw('
                    SUM(IFNULL(rs_tindakan,0) + IFNULL(rs_obat,0)) as rs_total,
                    SUM(IFNULL(pelayanan_tindakan,0) + IFNULL(pelayanan_obat,0)) as pelayanan_total,
                    SUM(IFNULL(total,0)) as grand_total,
                    COUNT(*) as count
                ')
                ->first();

            $rs = $stats->rs_total ?? 0;
            $pelayanan = $stats->pelayanan_total ?? 0;
            $total = $stats->grand_total ?? 0;

            // Apply Deductions for BPJS and JAMINAN
            if ($key === 'BPJS' || $key === 'JAMINAN') {
                $deductions = DB::table('penyesuaian_pendapatans')
                    ->where('kategori', $key)
                    ->whereBetween('tanggal', [$start, $end])
                    ->where('tahun', session('tahun_anggaran'))
                    ->selectRaw('SUM(potongan) as total_potongan, SUM(administrasi_bank) as total_adm')
                    ->first();

                $potongan = $deductions->total_potongan ?? 0;
                $adm = $deductions->total_adm ?? 0;

                if ($potongan > 0 || $adm > 0) {
                    // Potongan: 70% RS, 30% Pelayanan
                    $rs -= round($potongan * 0.7, 2);
                    $pelayanan -= round($potongan * 0.3, 2);

                    // Adm Bank: 100% RS
                    $rs -= $adm;

                    $total = $rs + $pelayanan;
                }
            }

            $data[$key] = [
                'rs' => $rs,
                'pelayanan' => $pelayanan,
                'total' => $total,
                'count' => (int) ($stats->count ?? 0)
            ];

            $totalRs += $rs;
            $totalPelayanan += $pelayanan;
            $totalAll += $total;
        }

        // Get breakdown by Room
        $roomData = $this->getRoomStatsWithDeductions($start, $end, session('tahun_anggaran'));
        $roomStats = $roomData['flat_total'];
        $roomPatientStats = $roomData['flat_count'];

        // Simple Patient Stats by Type
        $patientStats = [
            'UMUM' => $data['UMUM']['count'],
            'BPJS' => $data['BPJS']['count'],
            'JAMINAN' => $data['JAMINAN']['count'],
            'KERJASAMA' => $data['KERJASAMA']['count'],
            'LAINNYA' => $data['LAIN']['count']
        ];

        // New: Detailed Breakdown per Sub-Rekening
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
        $tahun = session('tahun_anggaran');

        foreach ($categories as $key => $meta) {
            $stats = $this->getDetailedBreakdown($key, $tahun, $start, $end);
            $breakdown[$key] = array_merge($meta, $stats);
        }

        return response()->json([
            'range' => ['start' => $start, 'end' => $end],
            'summary' => $data,
            'totals' => [
                'rs' => $totalRs,
                'pelayanan' => $totalPelayanan,
                'grand' => $totalAll
            ],
            'rooms' => $roomStats,
            'room_patients' => $roomPatientStats,
            'patients' => $patientStats,
            'breakdown' => $breakdown
        ]);
    }

    public function export(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT') || auth()->user()->hasPermission('LAPORAN_VIEW'), 403);

        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');

        // Reuse index logic but for export
        $tables = [
            'UMUM' => 'pendapatan_umum',
            'BPJS' => 'pendapatan_bpjs',
            'JAMINAN' => 'pendapatan_jaminan',
            'KERJASAMA' => 'pendapatan_kerjasama',
            'LAIN' => 'pendapatan_lain'
        ];

        $summary = [];
        foreach ($tables as $key => $table) {
            $stats = DB::table($table)
                ->whereBetween('tanggal', [$start, $end])
                ->where('tahun', $tahun)
                ->selectRaw('
                    SUM(IFNULL(rs_tindakan,0) + IFNULL(rs_obat,0)) as rs_total,
                    SUM(IFNULL(pelayanan_tindakan,0) + IFNULL(pelayanan_obat,0)) as pelayanan_total,
                    SUM(IFNULL(total,0)) as grand_total,
                    COUNT(*) as count
                ')
                ->first();

            $rs = $stats->rs_total ?? 0;
            $pelayanan = $stats->pelayanan_total ?? 0;
            $total = $stats->grand_total ?? 0;

            if ($key === 'BPJS' || $key === 'JAMINAN') {
                $ded = DB::table('penyesuaian_pendapatans')
                    ->where('kategori', $key)
                    ->whereBetween('tanggal', [$start, $end])
                    ->where('tahun', $tahun)
                    ->selectRaw('SUM(potongan) as total_potongan, SUM(administrasi_bank) as total_adm')
                    ->first();

                $potongan = $ded->total_potongan ?? 0;
                $adm = $ded->total_adm ?? 0;

                $rs -= round($potongan * 0.7, 2);
                $pelayanan -= round($potongan * 0.3, 2);
                $rs -= $adm;
                $total = $rs + $pelayanan;
            }

            $summary[$key] = [
                'rs' => $rs,
                'pelayanan' => $pelayanan,
                'total' => $total,
                'count' => (int) ($stats->count ?? 0)
            ];
        }

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
            $stats = $this->getDetailedBreakdown($key, $tahun, $start, $end);
            $breakdown[$key] = array_merge($meta, $stats);
        }

        // Room Stats
        $roomData = $this->getRoomStatsWithDeductions($start, $end, $tahun);
        $roomStats = $roomData['stats'];

        $filename = "Laporan_Pendapatan_{$start}_to_{$end}.xls";

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Penanda Tangan
        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        return view('dashboard.exports.pendapatan', [
            'start' => $start,
            'end' => $end,
            'summary' => $summary,
            'breakdown' => $breakdown,
            'rooms' => $roomStats,
            'tahun' => $tahun,
            'ptKiri' => $ptKiri,
            'ptTengah' => $ptTengah,
            'ptKanan' => $ptKanan
        ]);
    }

    public function exportPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT_PDF') || auth()->user()->hasPermission('LAPORAN_VIEW'), 403);

        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');

        $tables = [
            'UMUM' => 'pendapatan_umum',
            'BPJS' => 'pendapatan_bpjs',
            'JAMINAN' => 'pendapatan_jaminan',
            'KERJASAMA' => 'pendapatan_kerjasama',
            'LAIN' => 'pendapatan_lain'
        ];

        $summary = [];
        foreach ($tables as $key => $table) {
            $stats = DB::table($table)
                ->whereBetween('tanggal', [$start, $end])
                ->where('tahun', $tahun)
                ->selectRaw('
                    SUM(IFNULL(rs_tindakan,0) + IFNULL(rs_obat,0)) as rs_total,
                    SUM(IFNULL(pelayanan_tindakan,0) + IFNULL(pelayanan_obat,0)) as pelayanan_total,
                    SUM(IFNULL(total,0)) as grand_total,
                    COUNT(*) as count
                ')
                ->first();

            $rs = $stats->rs_total ?? 0;
            $pelayanan = $stats->pelayanan_total ?? 0;
            $total = $stats->grand_total ?? 0;

            if ($key === 'BPJS' || $key === 'JAMINAN') {
                $ded = DB::table('penyesuaian_pendapatans')
                    ->where('kategori', $key)
                    ->whereBetween('tanggal', [$start, $end])
                    ->where('tahun', $tahun)
                    ->selectRaw('SUM(potongan) as total_potongan, SUM(administrasi_bank) as total_adm')
                    ->first();

                $potongan = $ded->total_potongan ?? 0;
                $adm = $ded->total_adm ?? 0;

                $rs -= round($potongan * 0.7, 2);
                $pelayanan -= round($potongan * 0.3, 2);
                $rs -= $adm;
                $total = $rs + $pelayanan;
            }

            $summary[$key] = ['rs' => $rs, 'pelayanan' => $pelayanan, 'total' => $total, 'count' => (int) ($stats->count ?? 0)];
        }

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
            $stats = $this->getDetailedBreakdown($key, $tahun, $start, $end);
            $breakdown[$key] = array_merge($meta, $stats);
        }

        // Room Stats
        $roomData = $this->getRoomStatsWithDeductions($start, $end, $tahun);
        $roomStats = $roomData['stats'];

        // Penanda Tangan
        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        $pdf = Pdf::loadView('dashboard.exports.pendapatan_pdf', [
            'start' => $start,
            'end' => $end,
            'summary' => $summary,
            'breakdown' => $breakdown,
            'rooms' => $roomStats,
            'ptKiri' => $ptKiri,
            'ptTengah' => $ptTengah,
            'ptKanan' => $ptKanan
        ]);

        return $pdf->download("Laporan_Pendapatan_{$start}_to_{$end}.pdf");
    }



    private function getDetailedBreakdown($category, $tahun, $start, $end)
    {
        $query = null;
        $deductions = 0;

        switch ($category) {
            case 'PASIEN_UMUM':
                $query = DB::table('pendapatan_umum');
                break;
            case 'BPJS_JAMINAN':
                $bpjs = $this->getStatsFromQuery(DB::table('pendapatan_bpjs')->where('tahun', $tahun)->whereBetween('tanggal', [$start, $end]));
                $jam = $this->getStatsFromQuery(DB::table('pendapatan_jaminan')->where('tahun', $tahun)->whereBetween('tanggal', [$start, $end]));

                $ded = DB::table('penyesuaian_pendapatans')
                    ->whereIn('kategori', ['BPJS', 'JAMINAN'])
                    ->whereBetween('tanggal', [$start, $end])
                    ->where('tahun', $tahun)
                    ->selectRaw('SUM(potongan) as total_potongan, SUM(administrasi_bank) as total_adm')
                    ->first();
                $deductions = [
                    'potongan' => $ded->total_potongan ?? 0,
                    'adm' => $ded->total_adm ?? 0
                ];

                return $this->mergeStats($bpjs, $jam, $deductions);

            case 'KERJASAMA':
                $query = DB::table('pendapatan_kerjasama');
                break;
            case 'PKL':
                $query = DB::table('pendapatan_lain')->where(fn($q) => $q->where('transaksi', 'like', '%PKL%')->orWhere('transaksi', 'like', '%Praktek Kerja Lapangan%'));
                break;
            case 'MAGANG':
                $query = DB::table('pendapatan_lain')->where('transaksi', 'like', '%Magang%');
                break;
            case 'PENELITIAN':
                $query = DB::table('pendapatan_lain')->where('transaksi', 'like', '%Penelitian%');
                break;
            case 'PERMINTAAN_DATA':
                $query = DB::table('pendapatan_lain')->where('transaksi', 'like', '%Permintaan Data%');
                break;
            case 'STUDY_BANDING':
                $query = DB::table('pendapatan_lain')->where('transaksi', 'like', '%Study Banding%');
                break;
            case 'LAIN_LAIN':
                $query = DB::table('pendapatan_lain')
                    ->where('transaksi', 'NOT LIKE', '%PKL%')
                    ->where('transaksi', 'NOT LIKE', '%Praktek Kerja Lapangan%')
                    ->where('transaksi', 'NOT LIKE', '%Magang%')
                    ->where('transaksi', 'NOT LIKE', '%Penelitian%')
                    ->where('transaksi', 'NOT LIKE', '%Permintaan Data%')
                    ->where('transaksi', 'NOT LIKE', '%Study Banding%');
                break;
        }

        if ($query) {
            return $this->getStatsFromQuery($query->where('tahun', $tahun)->whereBetween('tanggal', [$start, $end]));
        }

        return $this->emptyStats();
    }

    private function getRoomStatsWithDeductions($start, $end, $tahun)
    {
        $tables = [
            'UMUM' => 'pendapatan_umum',
            'BPJS' => 'pendapatan_bpjs',
            'JAMINAN' => 'pendapatan_jaminan',
            'KERJASAMA' => 'pendapatan_kerjasama',
            'LAIN' => 'pendapatan_lain'
        ];

        // Hitung pengurang proporsional per tabel BPJS & Jaminan
        $tableDeductions = [];
        foreach (['BPJS' => 'pendapatan_bpjs', 'JAMINAN' => 'pendapatan_jaminan'] as $key => $table) {
            $ded = DB::table('penyesuaian_pendapatans')
                ->where('kategori', $key)
                ->whereBetween('tanggal', [$start, $end])
                ->where('tahun', $tahun)
                ->selectRaw('SUM(potongan) as total_potongan, SUM(administrasi_bank) as total_adm')
                ->first();

            $totalDed = ($ded->total_potongan ?? 0) + ($ded->total_adm ?? 0);

            if ($totalDed > 0) {
                $gross = DB::table($table)->whereBetween('tanggal', [$start, $end])->where('tahun', $tahun)->sum('total');
                $tableDeductions[$table] = [
                    'gross' => $gross,
                    'deduction' => $totalDed
                ];
            }
        }

        $roomStats = [];
        $roomPatientStats = [];

        foreach ($tables as $table) {
            $rooms = DB::table($table)
                ->join('ruangans', "$table.ruangan_id", '=', 'ruangans.id')
                ->select('ruangans.nama', DB::raw('SUM(total) as gross_total'), DB::raw('COUNT(*) as count'))
                ->whereBetween('tanggal', [$start, $end])
                ->where("$table.tahun", $tahun)
                ->groupBy('ruangans.nama')
                ->get();

            foreach ($rooms as $r) {
                if (!isset($roomStats[$r->nama])) {
                    $roomStats[$r->nama] = ['total' => 0, 'count' => 0];
                }

                $netRoomTotal = $r->gross_total;

                // Proporsional pengurangan
                if (isset($tableDeductions[$table]) && $tableDeductions[$table]['gross'] > 0) {
                    $ratio = $r->gross_total / $tableDeductions[$table]['gross'];
                    $roomDed = $tableDeductions[$table]['deduction'] * $ratio;
                    $netRoomTotal -= $roomDed;
                }

                $roomStats[$r->nama]['total'] += $netRoomTotal;
                $roomStats[$r->nama]['count'] += $r->count;

                $roomPatientStats[$r->nama] = ($roomPatientStats[$r->nama] ?? 0) + $r->count;
            }
        }

        // Sort by highest total 
        uasort($roomStats, fn($a, $b) => $b['total'] <=> $a['total']);

        $flatRoomTotal = [];
        foreach ($roomStats as $nama => $data) {
            $flatRoomTotal[$nama] = $data['total'];
        }

        return [
            'stats' => $roomStats,
            'flat_total' => $flatRoomTotal,
            'flat_count' => $roomPatientStats
        ];
    }

    private function getStatsFromQuery($query)
    {
        $data = $query->select(
            'metode_pembayaran',
            'bank',
            DB::raw('SUM(total) as total'),
            DB::raw('SUM(IFNULL(rs_tindakan, 0) + IFNULL(rs_obat, 0)) as total_rs'),
            DB::raw('SUM(IFNULL(pelayanan_tindakan, 0) + IFNULL(pelayanan_obat, 0)) as total_pelayanan')
        )
            ->groupBy('metode_pembayaran', 'bank')
            ->get();

        $tunai = 0;
        $nonTunai = 0;
        $brk = 0;
        $bsi = 0;
        $rs = 0;
        $pelayanan = 0;

        foreach ($data as $row) {
            $rs += $row->total_rs;
            $pelayanan += $row->total_pelayanan;

            if ($row->metode_pembayaran === 'TUNAI') {
                $tunai += $row->total;
                $brk += $row->total; // TUNAI goes to BRK
            } else {
                $nonTunai += $row->total;
                if ($row->bank === 'BRK' || $row->bank === 'Bank Riau Kepri Syariah') {
                    $brk += $row->total;
                } else if ($row->bank === 'BSI' || $row->bank === 'Bank Syariah Indonesia') {
                    $bsi += $row->total;
                }
            }
        }

        return [
            'jasa' => ['RS' => $rs, 'PELAYANAN' => $pelayanan, 'TOTAL' => $rs + $pelayanan],
            'payments' => ['TUNAI' => $tunai, 'NON_TUNAI' => $nonTunai, 'TOTAL' => $tunai + $nonTunai],
            'banks' => ['BRK' => $brk, 'BSI' => $bsi, 'TOTAL' => $brk + $bsi]
        ];
    }

    private function mergeStats($s1, $s2, $deductions = 0)
    {
        $potongan = is_array($deductions) ? ($deductions['potongan'] ?? 0) : 0;
        $adm = is_array($deductions) ? ($deductions['adm'] ?? 0) : (!is_array($deductions) ? $deductions : 0);
        $totalDed = $potongan + $adm;

        $res = [
            'jasa' => [
                'RS' => $s1['jasa']['RS'] + $s2['jasa']['RS'],
                'PELAYANAN' => $s1['jasa']['PELAYANAN'] + $s2['jasa']['PELAYANAN'],
            ],
            'payments' => [
                'TUNAI' => $s1['payments']['TUNAI'] + $s2['payments']['TUNAI'],
                'NON_TUNAI' => $s1['payments']['NON_TUNAI'] + $s2['payments']['NON_TUNAI'],
            ],
            'banks' => [
                'BRK' => $s1['banks']['BRK'] + $s2['banks']['BRK'],
                'BSI' => $s1['banks']['BSI'] + $s2['banks']['BSI'],
            ]
        ];

        // Apply Deductions to Jasa as done in Summary table:
        if ($potongan > 0 || $adm > 0) {
            $res['jasa']['RS'] -= round($potongan * 0.7, 2);
            $res['jasa']['PELAYANAN'] -= round($potongan * 0.3, 2);
            $res['jasa']['RS'] -= $adm;
        }

        // Deduct from NON_TUNAI and BRK reception (assuming deductions affect bank receipts)
        $res['payments']['NON_TUNAI'] -= $totalDed;
        $res['banks']['BRK'] -= $totalDed;

        $res['jasa']['TOTAL'] = $res['jasa']['RS'] + $res['jasa']['PELAYANAN'];
        $res['payments']['TOTAL'] = $res['payments']['TUNAI'] + $res['payments']['NON_TUNAI'];
        $res['banks']['TOTAL'] = $res['banks']['BRK'] + $res['banks']['BSI'];

        return $res;
    }

    private function emptyStats()
    {
        return [
            'jasa' => ['RS' => 0, 'PELAYANAN' => 0, 'TOTAL' => 0],
            'payments' => ['TUNAI' => 0, 'NON_TUNAI' => 0, 'TOTAL' => 0],
            'banks' => ['BRK' => 0, 'BSI' => 0, 'TOTAL' => 0]
        ];
    }

    public function getRekon(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_REKON') || auth()->user()->hasPermission('LAPORAN_VIEW'), 403);
        $tahun = session('tahun_anggaran');

        $rekKoran = DB::table('rekening_korans')
            ->where('tahun', $tahun)
            ->where('cd', 'C')
            ->select(DB::raw('MONTH(tanggal) as bulan'), DB::raw('SUM(jumlah) as total'))
            ->groupBy('bulan')
            ->get()
            ->pluck('total', 'bulan');

        $tables = ['pendapatan_umum', 'pendapatan_bpjs', 'pendapatan_jaminan', 'pendapatan_kerjasama', 'pendapatan_lain'];
        $revenues = [];
        foreach ($tables as $table) {
            $data = DB::table($table)
                ->where('tahun', $tahun)
                ->select(DB::raw('MONTH(tanggal) as bulan'), DB::raw('SUM(total) as total'))
                ->groupBy('bulan')
                ->get();
            foreach ($data as $d) {
                $revenues[$d->bulan] = ($revenues[$d->bulan] ?? 0) + $d->total;
            }
        }

        // Subtract Deductions (Potongan & Adm Bank) from revenues to match bank
        $deductions = DB::table('penyesuaian_pendapatans')
            ->where('tahun', $tahun)
            ->select(DB::raw('MONTH(tanggal) as bulan'), DB::raw('SUM(IFNULL(potongan, 0) + IFNULL(administrasi_bank, 0)) as total_ded'))
            ->groupBy('bulan')
            ->get()
            ->pluck('total_ded', 'bulan');

        foreach ($deductions as $bulan => $totalDed) {
            if (isset($revenues[$bulan])) {
                $revenues[$bulan] -= $totalDed;
            } else {
                $revenues[$bulan] = -$totalDed;
            }
        }

        $rekonData = [];
        $cumulativeDiff = 0;

        $namaBulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        for ($i = 1; $i <= 12; $i++) {
            $bank = (float) ($rekKoran[$i] ?? 0);
            $pend = (float) ($revenues[$i] ?? 0);
            $diff = $bank - $pend;
            $cumulativeDiff += $diff;

            $rekonData[] = [
                'tanggal' => $namaBulan[$i],
                'bank' => $bank,
                'pendapatan' => $pend,
                'selisih' => (float) $diff,
                'kumulatif' => (float) $cumulativeDiff
            ];
        }

        // Sort descending to keep UI behavior the same? 
        // Or keep ascending (Jan to Dec). Currently it displays in reverse on the original code due to dates sort but UI might handle reverse. Let's just return ASC for now.
        return response()->json($rekonData);
    }

    public function getPiutang(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_PIUTANG') || auth()->user()->hasPermission('LAPORAN_VIEW'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');

        // 1. Get Gross Piutang per Perusahaan
        $piutangData = DB::table('piutangs')
            ->join('perusahaans', 'piutangs.perusahaan_id', '=', 'perusahaans.id')
            ->where('piutangs.tahun', $tahun)
            ->whereBetween('piutangs.tanggal', [$start, $end])
            ->select(
                'piutangs.perusahaan_id',
                'perusahaans.nama as nama_perusahaan',
                DB::raw('SUM(jumlah_piutang) as total_piutang')
            )
            ->groupBy('piutangs.perusahaan_id', 'perusahaans.nama')
            ->get();

        // 2. Get Deductions per Perusahaan from the new table
        $penyesuaianData = DB::table('penyesuaian_pendapatans')
            ->where('tahun', $tahun)
            ->whereBetween('tanggal', [$start, $end])
            ->select(
                'perusahaan_id',
                DB::raw('SUM(potongan) as total_potongan'),
                DB::raw('SUM(administrasi_bank) as total_adm_bank')
            )
            ->groupBy('perusahaan_id')
            ->get()
            ->keyBy('perusahaan_id');

        // 3. Merge
        $finalData = $piutangData->map(function ($item) use ($penyesuaianData) {
            $pen = $penyesuaianData->get($item->perusahaan_id);
            $item->total_potongan = $pen->total_potongan ?? 0;
            $item->total_adm_bank = $pen->total_adm_bank ?? 0;
            // Netto = Gross - (Potongan + Adm)
            $item->total_dibayar = $item->total_piutang - ($item->total_potongan + $item->total_adm_bank);
            return $item;
        });

        // 4. Handle cases where there are deductions but no gross piutang in the period
        $piutangPerusahaanIds = $piutangData->pluck('perusahaan_id')->toArray();
        foreach ($penyesuaianData as $id => $pen) {
            if (!in_array($id, $piutangPerusahaanIds)) {
                $perusahaan = DB::table('perusahaans')->where('id', $id)->first();
                $finalData->push((object) [
                    'perusahaan_id' => $id,
                    'nama_perusahaan' => $perusahaan->nama ?? 'Unknown',
                    'total_piutang' => 0,
                    'total_potongan' => $pen->total_potongan,
                    'total_adm_bank' => $pen->total_adm_bank,
                    'total_dibayar' => -($pen->total_potongan + $pen->total_adm_bank)
                ]);
            }
        }

        $totals = [
            'piutang' => $finalData->sum('total_piutang'),
            'potongan' => $finalData->sum('total_potongan'),
            'adm_bank' => $finalData->sum('total_adm_bank'),
            'dibayar' => $finalData->sum('total_dibayar')
        ];

        return response()->json(['data' => $finalData, 'totals' => $totals]);
    }

    public function getMou(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_MOU') || auth()->user()->hasPermission('LAPORAN_VIEW'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');

        // 1. Kerjasama (Join MOU)
        $kerjasama = DB::table('pendapatan_kerjasama')
            ->join('mous', 'pendapatan_kerjasama.mou_id', '=', 'mous.id')
            ->where('pendapatan_kerjasama.tahun', $tahun)
            ->whereBetween('pendapatan_kerjasama.tanggal', [$start, $end])
            ->select(
                'mous.nama as nama_mou',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(rs_tindakan + rs_obat) as rs'),
                DB::raw('SUM(pelayanan_tindakan + pelayanan_obat) as pelayanan'),
                DB::raw('SUM(total) as total'),
                DB::raw('0 as potongan'),
                DB::raw('0 as adm_bank')
            )
            ->groupBy('mous.nama');

        // 2. Pendapatan Lain (Join MOU)
        $lain = DB::table('pendapatan_lain')
            ->join('mous', 'pendapatan_lain.mou_id', '=', 'mous.id')
            ->where('pendapatan_lain.tahun', $tahun)
            ->whereBetween('pendapatan_lain.tanggal', [$start, $end])
            ->select(
                'mous.nama as nama_mou',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(rs_tindakan + rs_obat) as rs'),
                DB::raw('SUM(pelayanan_tindakan + pelayanan_obat) as pelayanan'),
                DB::raw('SUM(total) as total'),
                DB::raw('0 as potongan'),
                DB::raw('0 as adm_bank')
            )
            ->groupBy('mous.nama');

        // 3. BPJS (Join Perusahaan)
        $bpjs = DB::table('pendapatan_bpjs')
            ->join('perusahaans', 'pendapatan_bpjs.perusahaan_id', '=', 'perusahaans.id')
            ->where('pendapatan_bpjs.tahun', $tahun)
            ->whereBetween('pendapatan_bpjs.tanggal', [$start, $end])
            ->select(
                'perusahaans.nama as nama_mou',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(rs_tindakan + rs_obat) as rs'),
                DB::raw('SUM(pelayanan_tindakan + pelayanan_obat) as pelayanan'),
                DB::raw('SUM(total) as total'),
                DB::raw('0 as potongan'),
                DB::raw('0 as adm_bank')
            )
            ->groupBy('perusahaans.nama');

        // 4. Jaminan (Join Perusahaan)
        $jaminan = DB::table('pendapatan_jaminan')
            ->join('perusahaans', 'pendapatan_jaminan.perusahaan_id', '=', 'perusahaans.id')
            ->where('pendapatan_jaminan.tahun', $tahun)
            ->whereBetween('pendapatan_jaminan.tanggal', [$start, $end])
            ->select(
                'perusahaans.nama as nama_mou',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(rs_tindakan + rs_obat) as rs'),
                DB::raw('SUM(pelayanan_tindakan + pelayanan_obat) as pelayanan'),
                DB::raw('SUM(total) as total'),
                DB::raw('0 as potongan'),
                DB::raw('0 as adm_bank')
            )
            ->groupBy('perusahaans.nama');

        // 5. Penyesuaian (New Part) - only join perusahaans since penyesuaian table hanya ada perusahaan_id
        $penyesuaian = DB::table('penyesuaian_pendapatans')
            ->join('perusahaans', 'penyesuaian_pendapatans.perusahaan_id', '=', 'perusahaans.id')
            ->where('penyesuaian_pendapatans.tahun', $tahun)
            ->whereBetween('penyesuaian_pendapatans.tanggal', [$start, $end])
            ->select(
                'perusahaans.nama as nama_mou',
                DB::raw('0 as count'),
                DB::raw('0 as rs'),
                DB::raw('0 as pelayanan'),
                DB::raw('0 as total'),
                DB::raw('SUM(potongan) as potongan'),
                DB::raw('SUM(administrasi_bank) as adm_bank')
            )
            ->groupBy('perusahaans.nama');

        // Combine using Union All for raw results
        $combined = $kerjasama->unionAll($lain)->unionAll($bpjs)->unionAll($jaminan)->unionAll($penyesuaian);

        $final = DB::table(DB::raw("({$combined->toSql()}) as merged"))
            ->mergeBindings($combined)
            ->select(
                'nama_mou',
                DB::raw('SUM(count) as count'),
                DB::raw('SUM(rs) as rs'),
                DB::raw('SUM(pelayanan) as pelayanan'),
                DB::raw('SUM(total) as gross'),
                DB::raw('SUM(potongan) as potongan'),
                DB::raw('SUM(adm_bank) as adm_bank'),
                DB::raw('SUM(total) - (SUM(potongan) + SUM(adm_bank)) as total')
            )
            ->groupBy('nama_mou')
            ->orderByDesc('total')
            ->get();

        return response()->json($final);
    }

    public function getAnggaran(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_ANGGARAN') || auth()->user()->hasPermission('LAPORAN_VIEW'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');

        // Dates for calculation
        $startOfYear = $tahun . '-01-01';
        $prevEnd = Carbon::parse($start)->subDay()->toDateString(); // Day before start date

        // Eager load children recursively
        $category = $request->get('category', 'SEMUA');
        $query = KodeRekening::with('children')
            ->whereNull('parent_id')
            ->orderBy('category', 'asc') // PENDAPATAN first
            ->orderBy('kode');

        if ($category !== 'SEMUA') {
            $query->where('category', $category);
        }

        $roots = $query->get();

        $report = [];
        foreach ($roots as $root) {
            $this->processLraNode($root, $tahun, $start, $end, $startOfYear, $prevEnd, $report);
        }

        $totalTarget = 0;
        $totalRealLalu = 0;
        $totalRealKini = 0;
        $totalRealTotal = 0;

        $pendTotals = ['target' => 0, 'real' => 0];
        $pengTotals = ['target' => 0, 'real' => 0];

        foreach ($report as $item) {
            // Sum if it's a top-level category (Pendapatan or Belanja)
            // Assuming 4 and 5 are now our roots.
            // We check against the IDs of the roots we fetched.
            $isRoot = $roots->contains('id', $item['id']);

            if ($isRoot) {
                // Collect sub-totals
                if ($item['category'] === 'PENDAPATAN') {
                    $pendTotals['target'] += $item['target'];
                    $pendTotals['real'] += $item['realisasi_total'];
                } else {
                    $pengTotals['target'] += $item['target'];
                    $pengTotals['real'] += $item['realisasi_total'];
                }

                $multiplier = ($item['category'] === 'PENGELUARAN') ? -1 : 1;

                if ($category === 'SEMUA') {
                    $totalTarget += ($item['target'] * $multiplier);
                    $totalRealLalu += ($item['realisasi_lalu'] * $multiplier);
                    $totalRealKini += ($item['realisasi_kini'] * $multiplier);
                    $totalRealTotal += ($item['realisasi_total'] * $multiplier);
                } else {
                    $totalTarget += $item['target'];
                    $totalRealLalu += $item['realisasi_lalu'];
                    $totalRealKini += $item['realisasi_kini'];
                    $totalRealTotal += $item['realisasi_total'];
                }
            }
        }

        $res = [
            'data' => $report,
            'category' => $category,
            'totals' => [
                'target' => $totalTarget,
                'realisasi_lalu' => $totalRealLalu,
                'realisasi_kini' => $totalRealKini,
                'realisasi_total' => $totalRealTotal,
                'persen' => $totalTarget != 0 ? round(($totalRealTotal / abs($totalTarget)) * 100, 2) : 0
            ]
        ];

        if ($category === 'SEMUA') {
            $res['sub_totals'] = [
                'pendapatan' => [
                    'target' => $pendTotals['target'],
                    'real' => $pendTotals['real'],
                    'persen' => $pendTotals['target'] > 0 ? round(($pendTotals['real'] / $pendTotals['target']) * 100, 2) : 0
                ],
                'pengeluaran' => [
                    'target' => $pengTotals['target'],
                    'real' => $pengTotals['real'],
                    'persen' => $pengTotals['target'] > 0 ? round(($pengTotals['real'] / $pengTotals['target']) * 100, 2) : 0
                ]
            ];
        }

        return response()->json($res);
    }

    private function processLraNode($node, $tahun, $start, $end, $startOfYear, $prevEnd, &$flatList)
    {
        $target = 0;
        $realLalu = 0;
        $realKini = 0;
        $realTotal = 0;

        $childItems = [];
        if ($node->tipe === 'detail') {
            $target = DB::table('anggaran_rekening')
                ->where('kode_rekening_id', $node->id)
                ->where('tahun', $tahun)
                ->value('nilai') ?? 0;

            if ($node->sumber_data) {
                // Calculate Previous (Jan 1 to Start-1)
                // If starts on Jan 1, previous is 0
                if ($start > $startOfYear) {
                    $realLalu = $this->calculateRealisasiDetail($node->sumber_data, $tahun, $startOfYear, $prevEnd, $node->id);
                } else {
                    $realLalu = 0;
                }

                // Calculate Current (Start to End)
                $realKini = $this->calculateRealisasiDetail($node->sumber_data, $tahun, $start, $end, $node->id);

                $realTotal = $realLalu + $realKini;
            } elseif ($node->category === 'PENGELUARAN') {
                // Calculate by ID directly if no mapping
                if ($start > $startOfYear) {
                    $realLalu = DB::table('pengeluaran')
                        ->where('kode_rekening_id', $node->id)
                        ->whereBetween('tanggal', [$startOfYear, $prevEnd])
                        ->sum('nominal');
                }
                $realKini = DB::table('pengeluaran')
                    ->where('kode_rekening_id', $node->id)
                    ->whereBetween('tanggal', [$start, $end])
                    ->sum('nominal');
                $realTotal = $realLalu + $realKini;
            }
        } else {
            foreach ($node->children as $child) {
                $res = $this->processLraNode($child, $tahun, $start, $end, $startOfYear, $prevEnd, $childItems);
                $target += $res['target'];
                $realLalu += $res['realisasi_lalu'];
                $realKini += $res['realisasi_kini'];
                $realTotal += $res['realisasi_total'];
            }
        }

        $persen = $target > 0 ? round(($realTotal / $target) * 100, 2) : 0;

        $item = [
            'id' => $node->id,
            'kode' => $node->kode,
            'nama' => $node->nama,
            'level' => $node->level,
            'tipe' => $node->tipe,
            'category' => $node->category,
            'target' => $target,
            'realisasi_lalu' => $realLalu,
            'realisasi_kini' => $realKini,
            'realisasi_total' => $realTotal,
            'selisih' => $target - $realTotal,
            'persen' => $persen
        ];

        $flatList[] = $item;
        foreach ($childItems as $ci) {
            $flatList[] = $ci;
        }

        return $item;
    }

    private function calculateRealisasiDetail($sumberData, $tahun, $startDate, $endDate, $nodeId = null)
    {
        $total = 0;
        switch ($sumberData) {
            case 'PASIEN_UMUM':
                $total = DB::table('pendapatan_umum')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->sum('total');
                break;
            case 'BPJS_JAMINAN':
                $bpjs = DB::table('pendapatan_bpjs')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->sum('total');
                $jam = DB::table('pendapatan_jaminan')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->sum('total');

                // Subtract Deductions for BPJS and JAMINAN
                $deductions = DB::table('penyesuaian_pendapatans')
                    ->whereIn('kategori', ['BPJS', 'JAMINAN'])
                    ->whereBetween('tanggal', [$startDate, $endDate])
                    ->where('tahun', $tahun)
                    ->selectRaw('SUM(IFNULL(potongan, 0) + IFNULL(administrasi_bank, 0)) as total_ded')
                    ->first();

                $total = ($bpjs + $jam) - ($deductions->total_ded ?? 0);
                break;
            case 'KERJASAMA':
                $total = DB::table('pendapatan_kerjasama')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->sum('total');
                break;
            case 'PKL':
                $total = DB::table('pendapatan_lain')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])
                    ->where(fn($q) => $q->where('transaksi', 'like', '%PKL%')->orWhere('transaksi', 'like', '%Praktek Kerja Lapangan%'))->sum('total');
                break;
            case 'MAGANG':
                $total = DB::table('pendapatan_lain')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->where('transaksi', 'like', '%Magang%')->sum('total');
                break;
            case 'PENELITIAN':
                $total = DB::table('pendapatan_lain')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->where('transaksi', 'like', '%Penelitian%')->sum('total');
                break;
            case 'PERMINTAAN_DATA':
                $total = DB::table('pendapatan_lain')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->where('transaksi', 'like', '%Permintaan Data%')->sum('total');
                break;
            case 'STUDY_BANDING':
                $total = DB::table('pendapatan_lain')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])->where('transaksi', 'like', '%Study Banding%')->sum('total');
                break;
            case 'LAIN_LAIN':
                $total = DB::table('pendapatan_lain')->where('tahun', $tahun)->whereBetween('tanggal', [$startDate, $endDate])
                    ->where('transaksi', 'NOT LIKE', '%PKL%')
                    ->where('transaksi', 'NOT LIKE', '%Praktek Kerja Lapangan%')
                    ->where('transaksi', 'NOT LIKE', '%Magang%')
                    ->where('transaksi', 'NOT LIKE', '%Penelitian%')
                    ->where('transaksi', 'NOT LIKE', '%Permintaan Data%')
                    ->where('transaksi', 'NOT LIKE', '%Study Banding%')
                    ->sum('total');
                break;
            case 'PEGAWAI':
            case 'BARANG_JASA':
            case 'MODAL':
                $query = DB::table('pengeluaran')
                    ->where('kategori', $sumberData)
                    ->whereBetween('tanggal', [$startDate, $endDate]);

                if ($nodeId) {
                    $query->where('kode_rekening_id', $nodeId);
                }

                $total = $query->sum('nominal');
                break;
        }
        return $total;
    }

    public function exportRekon(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $data = $this->getRekon($request)->getData();

        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Laporan_Rekon_{$start}_to_{$end}.xls\"");
        return view('dashboard.exports.rekon', ['data' => $data, 'start' => $start, 'end' => $end, 'ptKiri' => $ptKiri, 'ptTengah' => $ptTengah, 'ptKanan' => $ptKanan]);
    }

    public function exportRekonPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT_PDF'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $data = $this->getRekon($request)->getData();

        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        $pdf = Pdf::loadView('dashboard.exports.rekon_pdf', ['data' => $data, 'start' => $start, 'end' => $end, 'ptKiri' => $ptKiri, 'ptTengah' => $ptTengah, 'ptKanan' => $ptKanan]);
        return $pdf->download("Laporan_Rekon_{$start}_to_{$end}.pdf");
    }

    public function exportPiutang(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $res = $this->getPiutang($request)->getData();

        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Laporan_Piutang_{$start}_to_{$end}.xls\"");
        return view('dashboard.exports.piutang', ['data' => $res->data, 'totals' => $res->totals, 'start' => $start, 'end' => $end, 'ptKiri' => $ptKiri, 'ptTengah' => $ptTengah, 'ptKanan' => $ptKanan]);
    }

    public function exportPiutangPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT_PDF'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $res = $this->getPiutang($request)->getData();

        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        $pdf = Pdf::loadView('dashboard.exports.piutang_pdf', ['data' => $res->data, 'totals' => $res->totals, 'start' => $start, 'end' => $end, 'ptKiri' => $ptKiri, 'ptTengah' => $ptTengah, 'ptKanan' => $ptKanan]);
        return $pdf->download("Laporan_Piutang_{$start}_to_{$end}.pdf");
    }

    public function exportMou(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $data = $this->getMou($request)->getData();

        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Laporan_MOU_{$start}_to_{$end}.xls\"");
        return view('dashboard.exports.mou', ['data' => $data, 'start' => $start, 'end' => $end, 'ptKiri' => $ptKiri, 'ptTengah' => $ptTengah, 'ptKanan' => $ptKanan]);
    }

    public function exportMouPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT_PDF'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $data = $this->getMou($request)->getData();

        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        $pdf = Pdf::loadView('dashboard.exports.mou_pdf', ['data' => $data, 'start' => $start, 'end' => $end, 'ptKiri' => $ptKiri, 'ptTengah' => $ptTengah, 'ptKanan' => $ptKanan]);
        return $pdf->download("Laporan_MOU_{$start}_to_{$end}.pdf");
    }

    public function exportAnggaran(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $res = $this->getAnggaran($request)->getData();

        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Laporan_Realisasi_Anggaran_{$start}_to_{$end}.xls\"");
        return view('dashboard.exports.anggaran', [
            'data' => $res->data,
            'totals' => $res->totals,
            'start' => $start,
            'end' => $end,
            'category' => $request->get('category', 'PENDAPATAN'),
            'ptKiri' => $ptKiri,
            'ptTengah' => $ptTengah,
            'ptKanan' => $ptKanan
        ]);
    }

    public function exportAnggaranPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT_PDF'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $res = $this->getAnggaran($request)->getData();

        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        $pdf = Pdf::loadView('dashboard.exports.anggaran_pdf', [
            'data' => $res->data,
            'totals' => $res->totals,
            'start' => $start,
            'end' => $end,
            'category' => $request->get('category', 'PENDAPATAN'),
            'ptKiri' => $ptKiri,
            'ptTengah' => $ptTengah,
            'ptKanan' => $ptKanan
        ]);
        return $pdf->download("Laporan_Realisasi_Anggaran_{$start}_to_{$end}.pdf");
    }

    public function exportPengeluaran(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $res = $this->getPengeluaran($request)->getData();

        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Laporan_Pengeluaran_{$start}_to_{$end}.xls\"");
        return view('dashboard.exports.pengeluaran', ['data' => $res->data, 'summary' => $res->summary, 'start' => $start, 'end' => $end, 'ptKiri' => $ptKiri, 'ptTengah' => $ptTengah, 'ptKanan' => $ptKanan]);
    }

    public function exportPengeluaranPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT_PDF'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $res = $this->getPengeluaran($request)->getData();

        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        $pdf = Pdf::loadView('dashboard.exports.pengeluaran_pdf', ['data' => $res->data, 'summary' => (array) $res->summary, 'start' => $start, 'end' => $end, 'ptKiri' => $ptKiri, 'ptTengah' => $ptTengah, 'ptKanan' => $ptKanan]);
        return $pdf->download("Laporan_Pengeluaran_{$start}_to_{$end}.pdf");
    }

    public function exportDpa(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT'), 403);
        $tahun = session('tahun_anggaran');
        $res = $this->getDpa($request)->getData();

        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Laporan_DPA_{$tahun}.xls\"");
        return view('dashboard.exports.dpa', ['data' => $res->data, 'tahun' => $tahun, 'ptKiri' => $ptKiri, 'ptTengah' => $ptTengah, 'ptKanan' => $ptKanan]);
    }

    public function exportDpaPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT_PDF'), 403);
        $tahun = session('tahun_anggaran');
        $res = $this->getDpa($request)->getData();

        $ptKiri = $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null;
        $ptTengah = $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null;
        $ptKanan = $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null;

        $pdf = Pdf::loadView('dashboard.exports.dpa_pdf', ['data' => $res->data, 'tahun' => $tahun, 'ptKiri' => $ptKiri, 'ptTengah' => $ptTengah, 'ptKanan' => $ptKanan]);
        return $pdf->download("Laporan_DPA_{$tahun}.pdf");
    }
}
