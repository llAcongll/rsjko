<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\KodeRekening;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
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
        $roomStats = [];
        foreach ($tables as $table) {
            $rooms = DB::table($table)
                ->join('ruangans', "$table.ruangan_id", '=', 'ruangans.id')
                ->select('ruangans.nama', DB::raw('SUM(total) as total'))
                ->whereBetween('tanggal', [$start, $end])
                ->where("$table.tahun", session('tahun_anggaran'))
                ->groupBy('ruangans.nama')
                ->get();

            foreach ($rooms as $r) {
                $roomStats[$r->nama] = ($roomStats[$r->nama] ?? 0) + $r->total;
            }
        }
        arsort($roomStats);

        // Patient Stats per Room (following roomStats logic)
        $roomPatientStats = [];
        foreach ($tables as $table) {
            $rooms = DB::table($table)
                ->join('ruangans', "$table.ruangan_id", '=', 'ruangans.id')
                ->select('ruangans.nama', DB::raw('COUNT(*) as count'))
                ->whereBetween('tanggal', [$start, $end])
                ->where("$table.tahun", session('tahun_anggaran'))
                ->groupBy('ruangans.nama')
                ->get();

            foreach ($rooms as $r) {
                $roomPatientStats[$r->nama] = ($roomPatientStats[$r->nama] ?? 0) + $r->count;
            }
        }
        arsort($roomPatientStats);

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
        $roomStats = [];
        foreach ($tables as $table) {
            $rooms = DB::table($table)
                ->join('ruangans', "$table.ruangan_id", '=', 'ruangans.id')
                ->select('ruangans.nama', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
                ->whereBetween('tanggal', [$start, $end])
                ->where("$table.tahun", $tahun)
                ->groupBy('ruangans.nama')
                ->get();

            foreach ($rooms as $r) {
                if (!isset($roomStats[$r->nama])) {
                    $roomStats[$r->nama] = ['total' => 0, 'count' => 0];
                }
                $roomStats[$r->nama]['total'] += $r->total;
                $roomStats[$r->nama]['count'] += $r->count;
            }
        }
        uasort($roomStats, fn($a, $b) => $b['total'] <=> $a['total']);

        $filename = "Laporan_Pendapatan_{$start}_to_{$end}.xls";

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        return view('dashboard.exports.pendapatan', [
            'start' => $start,
            'end' => $end,
            'summary' => $summary,
            'breakdown' => $breakdown,
            'rooms' => $roomStats,
            'tahun' => $tahun
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

        $roomStats = [];
        foreach ($tables as $table) {
            $rooms = DB::table($table)
                ->join('ruangans', "$table.ruangan_id", '=', 'ruangans.id')
                ->select('ruangans.nama', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
                ->whereBetween('tanggal', [$start, $end])
                ->where("$table.tahun", $tahun)
                ->groupBy('ruangans.nama')
                ->get();

            foreach ($rooms as $r) {
                if (!isset($roomStats[$r->nama])) {
                    $roomStats[$r->nama] = ['total' => 0, 'count' => 0];
                }
                $roomStats[$r->nama]['total'] += $r->total;
                $roomStats[$r->nama]['count'] += $r->count;
            }
        }
        uasort($roomStats, fn($a, $b) => $b['total'] <=> $a['total']);

        $pdf = Pdf::loadView('dashboard.exports.pendapatan_pdf', [
            'start' => $start,
            'end' => $end,
            'summary' => $summary,
            'breakdown' => $breakdown,
            'rooms' => $roomStats
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
                    ->selectRaw('SUM(IFNULL(potongan, 0) + IFNULL(administrasi_bank, 0)) as total_ded')
                    ->first();
                $deductions = $ded->total_ded ?? 0;

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

    private function getStatsFromQuery($query)
    {
        $data = $query->select('metode_pembayaran', 'bank', DB::raw('SUM(total) as total'))
            ->groupBy('metode_pembayaran', 'bank')
            ->get();

        $tunai = 0;
        $nonTunai = 0;
        $brk = 0;
        $bsi = 0;

        foreach ($data as $row) {
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
            'payments' => ['TUNAI' => $tunai, 'NON_TUNAI' => $nonTunai, 'TOTAL' => $tunai + $nonTunai],
            'banks' => ['BRK' => $brk, 'BSI' => $bsi, 'TOTAL' => $brk + $bsi]
        ];
    }

    private function mergeStats($s1, $s2, $deductions = 0)
    {
        $res = [
            'payments' => [
                'TUNAI' => $s1['payments']['TUNAI'] + $s2['payments']['TUNAI'],
                'NON_TUNAI' => $s1['payments']['NON_TUNAI'] + $s2['payments']['NON_TUNAI'],
            ],
            'banks' => [
                'BRK' => $s1['banks']['BRK'] + $s2['banks']['BRK'],
                'BSI' => $s1['banks']['BSI'] + $s2['banks']['BSI'],
            ]
        ];

        // Deduct from NON_TUNAI and BRK reception (assuming deductions affect bank receipts)
        $res['payments']['NON_TUNAI'] -= $deductions;
        $res['banks']['BRK'] -= $deductions;

        $res['payments']['TOTAL'] = $res['payments']['TUNAI'] + $res['payments']['NON_TUNAI'];
        $res['banks']['TOTAL'] = $res['banks']['BRK'] + $res['banks']['BSI'];

        return $res;
    }

    private function emptyStats()
    {
        return [
            'payments' => ['TUNAI' => 0, 'NON_TUNAI' => 0, 'TOTAL' => 0],
            'banks' => ['BRK' => 0, 'BSI' => 0, 'TOTAL' => 0]
        ];
    }

    public function getRekon(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_REKON') || auth()->user()->hasPermission('LAPORAN_VIEW'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());

        $rekKoran = DB::table('rekening_korans')
            ->whereBetween('tanggal', [$start, $end])
            ->where('tahun', session('tahun_anggaran'))
            ->where('cd', 'C')
            ->select(DB::raw('DATE(tanggal) as tgl'), DB::raw('SUM(jumlah) as total'))
            ->groupBy('tgl')
            ->get()
            ->pluck('total', 'tgl');

        $tables = ['pendapatan_umum', 'pendapatan_bpjs', 'pendapatan_jaminan', 'pendapatan_kerjasama', 'pendapatan_lain'];
        $revenues = [];
        foreach ($tables as $table) {
            $data = DB::table($table)
                ->whereBetween('tanggal', [$start, $end])
                ->where('tahun', session('tahun_anggaran'))
                ->select(DB::raw('DATE(tanggal) as tgl'), DB::raw('SUM(total) as total'))
                ->groupBy('tgl')
                ->get();
            foreach ($data as $d) {
                $revenues[$d->tgl] = ($revenues[$d->tgl] ?? 0) + $d->total;
            }
        }

        // Subtract Deductions (Potongan & Adm Bank) from revenues to match bank
        $deductions = DB::table('penyesuaian_pendapatans')
            ->whereBetween('tanggal', [$start, $end])
            ->where('tahun', session('tahun_anggaran'))
            ->select(DB::raw('DATE(tanggal) as tgl'), DB::raw('SUM(IFNULL(potongan, 0) + IFNULL(administrasi_bank, 0)) as total_ded'))
            ->groupBy('tgl')
            ->get()
            ->pluck('total_ded', 'tgl');

        foreach ($deductions as $tgl => $totalDed) {
            if (isset($revenues[$tgl])) {
                $revenues[$tgl] -= $totalDed;
            } else {
                // If there's a deduction on a day without gross revenue recorded in those tables 
                // (e.g., historical piutang payment with just deduction recorded), we treat it as negative revenue adjustment
                $revenues[$tgl] = -$totalDed;
            }
        }

        // Sort ASC for cumulative calculation
        $dates = collect(array_keys($rekKoran->toArray()))->merge(array_keys($revenues))->unique()->sort();

        $rekonData = [];
        $cumulativeDiff = 0;

        foreach ($dates as $date) {
            $bank = (float) ($rekKoran[$date] ?? 0);
            $pend = (float) ($revenues[$date] ?? 0);
            $diff = $bank - $pend;
            $cumulativeDiff += $diff;

            $rekonData[] = [
                'tanggal' => $date,
                'bank' => $bank,
                'pendapatan' => $pend,
                'selisih' => (float) $diff,
                'kumulatif' => (float) $cumulativeDiff
            ];
        }

        // Return reversed (latest first) for better UI presentation
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

        // Eager load children recursively
        $roots = KodeRekening::with('children')->whereNull('parent_id')->orderBy('kode')->get();

        $report = [];
        foreach ($roots as $root) {
            $this->processLraNode($root, $tahun, $start, $end, $report);
        }

        $totalTarget = 0;
        $totalReal = 0;
        foreach ($report as $item) {
            if ($item['level'] == 1) {
                $totalTarget += $item['target'];
                $totalReal += $item['realisasi'];
            }
        }

        return response()->json([
            'data' => $report,
            'totals' => [
                'target' => $totalTarget,
                'realisasi' => $totalReal,
                'persen' => $totalTarget > 0 ? round(($totalReal / $totalTarget) * 100, 2) : 0
            ]
        ]);
    }

    private function processLraNode($node, $tahun, $start, $end, &$flatList)
    {
        $target = 0;
        $real = 0;

        $childItems = [];
        if ($node->tipe === 'detail') {
            $target = DB::table('anggaran_rekening')
                ->where('kode_rekening_id', $node->id)
                ->where('tahun', $tahun)
                ->value('nilai') ?? 0;

            if ($node->sumber_data) {
                $real = $this->calculateRealisasiDetail($node->sumber_data, $tahun, $start, $end);
            }
        } else {
            foreach ($node->children as $child) {
                $res = $this->processLraNode($child, $tahun, $start, $end, $childItems);
                $target += $res['target'];
                $real += $res['realisasi'];
            }
        }

        $persen = $target > 0 ? round(($real / $target) * 100, 2) : 0;

        $item = [
            'id' => $node->id,
            'kode' => $node->kode,
            'nama' => $node->nama,
            'level' => $node->level,
            'tipe' => $node->tipe,
            'target' => $target,
            'realisasi' => $real,
            'selisih' => $target - $real,
            'persen' => $persen
        ];

        $flatList[] = $item;
        foreach ($childItems as $ci) {
            $flatList[] = $ci;
        }

        return $item;
    }

    private function calculateRealisasiDetail($sumberData, $tahun, $startDate, $endDate)
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
        }
        return $total;
    }

    public function exportRekon(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $data = $this->getRekon($request)->getData();

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Laporan_Rekon_{$start}_to_{$end}.xls\"");
        return view('dashboard.exports.rekon', ['data' => $data, 'start' => $start, 'end' => $end]);
    }

    public function exportRekonPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT_PDF'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $data = $this->getRekon($request)->getData();

        $pdf = Pdf::loadView('dashboard.exports.rekon_pdf', ['data' => $data, 'start' => $start, 'end' => $end]);
        return $pdf->download("Laporan_Rekon_{$start}_to_{$end}.pdf");
    }

    public function exportPiutang(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $res = $this->getPiutang($request)->getData();

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Laporan_Piutang_{$start}_to_{$end}.xls\"");
        return view('dashboard.exports.piutang', ['data' => $res->data, 'totals' => $res->totals, 'start' => $start, 'end' => $end]);
    }

    public function exportPiutangPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT_PDF'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $res = $this->getPiutang($request)->getData();

        $pdf = Pdf::loadView('dashboard.exports.piutang_pdf', ['data' => $res->data, 'totals' => $res->totals, 'start' => $start, 'end' => $end]);
        return $pdf->download("Laporan_Piutang_{$start}_to_{$end}.pdf");
    }

    public function exportMou(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $data = $this->getMou($request)->getData();

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Laporan_MOU_{$start}_to_{$end}.xls\"");
        return view('dashboard.exports.mou', ['data' => $data, 'start' => $start, 'end' => $end]);
    }

    public function exportMouPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT_PDF'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $data = $this->getMou($request)->getData();

        $pdf = Pdf::loadView('dashboard.exports.mou_pdf', ['data' => $data, 'start' => $start, 'end' => $end]);
        return $pdf->download("Laporan_MOU_{$start}_to_{$end}.pdf");
    }

    public function exportAnggaran(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $res = $this->getAnggaran($request)->getData();

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Laporan_Realisasi_Anggaran_{$start}_to_{$end}.xls\"");
        return view('dashboard.exports.anggaran', ['data' => $res->data, 'totals' => $res->totals, 'start' => $start, 'end' => $end]);
    }

    public function exportAnggaranPdf(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('LAPORAN_EXPORT_PDF'), 403);
        $start = $request->get('start', '2026-01-01');
        $end = $request->get('end', Carbon::now()->toDateString());
        $res = $this->getAnggaran($request)->getData();

        $pdf = Pdf::loadView('dashboard.exports.anggaran_pdf', ['data' => $res->data, 'totals' => $res->totals, 'start' => $start, 'end' => $end]);
        return $pdf->download("Laporan_Realisasi_Anggaran_{$start}_to_{$end}.pdf");
    }
}
