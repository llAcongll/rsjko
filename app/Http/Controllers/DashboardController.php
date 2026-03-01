<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller as BaseController;
use Carbon\Carbon;

class DashboardController extends BaseController
{
    /* =========================
       PAGE CONTENT
    ========================= */
    public function content(string $page, ?string $param = null)
    {
        return match ($page) {

            'dashboard' => view('dashboard.pages.dashboard'),

            'users' => (Auth::user()->isAdmin() || Auth::user()->hasPermission('USER_VIEW'))
            ? view('dashboard.pages.users', [
                'users' => User::orderBy('username')->get()
            ])
            : abort(403),

            'pendapatan' => match ($param) {
                    'UMUM' => (Auth::user()->hasPermission('PENDAPATAN_UMUM_VIEW') || Auth::user()->hasPermission('PENDAPATAN_UMUM')) ? view('dashboard.pages.pendapatan.umum') : abort(403),
                    'BPJS' => (Auth::user()->hasPermission('PENDAPATAN_BPJS_VIEW') || Auth::user()->hasPermission('PENDAPATAN_BPJS')) ? view('dashboard.pages.pendapatan.bpjs') : abort(403),
                    'JAMINAN' => (Auth::user()->hasPermission('PENDAPATAN_JAMINAN_VIEW') || Auth::user()->hasPermission('PENDAPATAN_JAMINAN')) ? view('dashboard.pages.pendapatan.jaminan') : abort(403),
                    'KERJASAMA' => (Auth::user()->hasPermission('PENDAPATAN_KERJA_VIEW') || Auth::user()->hasPermission('PENDAPATAN_KERJA')) ? view('dashboard.pages.pendapatan.kerjasama') : abort(403),
                    'LAIN' => (Auth::user()->hasPermission('PENDAPATAN_LAIN_VIEW') || Auth::user()->hasPermission('PENDAPATAN_LAIN')) ? view('dashboard.pages.pendapatan.lainlain') : abort(403),
                    'ANGGARAN' => Auth::user()->hasPermission('KODE_REKENING_VIEW') ? view('dashboard.pages.pendapatan.anggaran') : abort(403),
                    default => abort(404),
                },

            'laporan' => match ($param) {
                    'PENDAPATAN', 'REKON', 'PIUTANG', 'MOU', 'ANGGARAN', 'PENGELUARAN', 'DPA' => Auth::user()->hasPermission('LAPORAN_VIEW') ? view("dashboard.pages.laporan." . strtolower($param)) : abort(403),
                    default => Auth::user()->hasPermission('LAPORAN_VIEW') ? view('dashboard.pages.laporan.pendapatan') : abort(403),
                },
            'rekening' => Auth::user()->hasPermission('REKENING_VIEW') ? view('dashboard.pages.rekening') : abort(403),

            'ruangan' => Auth::user()->hasPermission('MASTER_RUANGAN_VIEW') || Auth::user()->hasPermission('MASTER_VIEW') ? view('dashboard.pages.ruangan') : abort(403),
            'perusahaan' => Auth::user()->hasPermission('MASTER_PERUSAHAAN_VIEW') || Auth::user()->hasPermission('MASTER_VIEW') ? view('dashboard.pages.perusahaan') : abort(403),
            'mou' => Auth::user()->hasPermission('MASTER_MOU_VIEW') || Auth::user()->hasPermission('MASTER_VIEW') ? view('dashboard.pages.mou') : abort(403),
            'penanda_tangan' => Auth::user()->hasPermission('MASTER_VIEW') ? view('dashboard.pages.penanda_tangan') : abort(403),
            'piutang' => Auth::user()->hasPermission('PIUTANG_VIEW') ? view('dashboard.pages.piutang') : abort(403),
            'penyesuaian' => Auth::user()->hasPermission('PENYESUAIAN_VIEW') ? view('dashboard.pages.penyesuaian') : abort(403),

            'master' => match ($param) {
                    'kode-rekening' => Auth::user()->hasPermission('KODE_REKENING_VIEW') ? (
                        request('category') === 'PENGELUARAN'
                        ? view('dashboard.master.kode-rekening.expenditure')
                        : view('dashboard.master.kode-rekening.index')
                    ) : abort(403),
                    'kode-rekening-anggaran' => Auth::user()->hasPermission('KODE_REKENING_VIEW') ? (
                        request('category') === 'PENGELUARAN'
                        ? view('dashboard.pages.pengeluaran.anggaran')
                        : view('dashboard.pages.pendapatan.anggaran')
                    ) : abort(403),
                    'logs' => (Auth::user()->isAdmin() || Auth::user()->hasPermission('ACTIVITY_LOG_VIEW')) ? view('dashboard.pages.master.logs') : abort(403),
                    default => abort(404),
                },

            'pengeluaran' => match ($param) {
                    'PEGAWAI', 'BARANG_JASA', 'MODAL' => Auth::user()->hasPermission('PENGELUARAN_VIEW') || Auth::user()->isAdmin() ? view('dashboard.pages.pengeluaran.index', ['param' => $param]) : abort(403),
                    'ANGGARAN' => (Auth::user()->hasPermission('KODE_REKENING_PENGELUARAN_VIEW') || Auth::user()->hasPermission('KODE_REKENING_VIEW') || Auth::user()->isAdmin()) ? view('dashboard.pages.pengeluaran.anggaran') : abort(403),
                    'disbursement' => (Auth::user()->hasPermission('PENGELUARAN_SPP_VIEW') || Auth::user()->hasPermission('PENGELUARAN_SPM_VIEW') || Auth::user()->hasPermission('PENGELUARAN_SP2D_VIEW') || Auth::user()->hasPermission('PENGELUARAN_CAIR_VIEW') || Auth::user()->isAdmin()) ? view('dashboard.pages.pengeluaran.disbursement') : abort(403),
                    'saldo' => (Auth::user()->hasPermission('PENGELUARAN_SALDO_VIEW') || Auth::user()->isAdmin()) ? view('dashboard.pages.pengeluaran.saldo') : abort(403),
                    'ledger' => (Auth::user()->hasPermission('PENGELUARAN_BKU_VIEW') || Auth::user()->isAdmin()) ? view('dashboard.pages.pengeluaran.ledger') : abort(403),
                    'rekening-koran' => (Auth::user()->hasPermission('PENGELUARAN_RK_VIEW') || Auth::user()->isAdmin()) ? view('dashboard.pages.pengeluaran.rekening-koran') : abort(403),
                    default => abort(404),
                },

            'pengesahan' => match ($param) {
                    'SP3BP', 'sp3bp' => Auth::user()->hasPermission('PENGESAHAN_VIEW') ? view('dashboard.pages.pengesahan.sp3bp') : abort(403),
                    'SPTJB', 'sptjb' => Auth::user()->hasPermission('PENGESAHAN_VIEW') ? view('dashboard.pages.pengesahan.sptjb') : abort(403),
                    'LRKB', 'lrkb' => Auth::user()->hasPermission('PENGESAHAN_VIEW') ? view('dashboard.pages.pengesahan.lrkb') : abort(403),
                    default => abort(404),
                },

            default => abort(404),
        };
    }

    /* =========================
       DASHBOARD SUMMARY (REAL)
    ========================= */
    public function summary()
    {
        try {
            $tahunAnggaran = session('tahun_anggaran') ?? now()->year;
            $tables = ['pendapatan_umum', 'pendapatan_bpjs', 'pendapatan_jaminan', 'pendapatan_kerjasama', 'pendapatan_lain'];

            /* 1. TARGET ANGGARAN */
            // Target Pendapatan (Hanya Kode Rekening Category PENDAPATAN)
            $targetPendapatan = DB::table('anggaran_rekening')
                ->join('kode_rekening', 'anggaran_rekening.kode_rekening_id', '=', 'kode_rekening.id')
                ->where('anggaran_rekening.tahun', $tahunAnggaran)
                ->where('kode_rekening.category', 'PENDAPATAN')
                ->sum('nilai');

            // Target Pengeluaran (Hanya Kode Rekening Category PENGELUARAN)
            $targetPengeluaran = DB::table('anggaran_rekening')
                ->join('kode_rekening', 'anggaran_rekening.kode_rekening_id', '=', 'kode_rekening.id')
                ->where('anggaran_rekening.tahun', $tahunAnggaran)
                ->where('kode_rekening.category', 'PENGELUARAN')
                ->sum('nilai');

            /* 2. REALISASI & BREAKDOWN (RS vs PELAYANAN) */
            $totalRS = 0;
            $totalPelayanan = 0;

            foreach ($tables as $tbl) {
                $sums = DB::table($tbl)
                    ->join('revenue_masters', $tbl . '.revenue_master_id', '=', 'revenue_masters.id')
                    ->where($tbl . '.tahun', $tahunAnggaran)
                    ->where('revenue_masters.is_posted', true)
                    ->select(
                        DB::raw('SUM(' . $tbl . '.rs_tindakan + ' . $tbl . '.rs_obat) as rs'),
                        DB::raw('SUM(' . $tbl . '.pelayanan_tindakan + ' . $tbl . '.pelayanan_obat) as pelayanan')
                    )
                    ->first();

                $totalRS += ($sums->rs ?? 0);
                $totalPelayanan += ($sums->pelayanan ?? 0);
            }

            /* 3. DEDUCTIONS (POTONGAN & ADM BANK) */
            $totalPotongan = DB::table('penyesuaian_pendapatans')
                ->where('tahun', $tahunAnggaran)
                ->sum(DB::raw('IFNULL(potongan, 0) + IFNULL(administrasi_bank, 0)'));

            $realisasiGross = $totalRS + $totalPelayanan;
            $realisasiNet = max(0, $realisasiGross - $totalPotongan);

            /* 4. REALISASI PENGELUARAN (ECONOMIC) */
            $realisasiPengeluaran = DB::table('expenditures')
                ->whereYear('spending_date', $tahunAnggaran)
                ->sum('gross_value');

            /* 5. PERSENTASE CAPAIAN */
            $persenCapaian = $targetPendapatan > 0 ? round(($realisasiNet / $targetPendapatan) * 100, 2) : 0;
            $persenCapaianPengeluaran = $targetPengeluaran > 0 ? round(($realisasiPengeluaran / $targetPengeluaran) * 100, 2) : 0;

            /* 6. DISTRIBUSI PASIEN (Pie Chart) */
            $incUmum = DB::table('pendapatan_umum')
                ->join('revenue_masters', 'pendapatan_umum.revenue_master_id', '=', 'revenue_masters.id')
                ->where('pendapatan_umum.tahun', $tahunAnggaran)
                ->where('revenue_masters.is_posted', true)
                ->sum('pendapatan_umum.total');
            $incBpjs = DB::table('pendapatan_bpjs')
                ->join('revenue_masters', 'pendapatan_bpjs.revenue_master_id', '=', 'revenue_masters.id')
                ->where('pendapatan_bpjs.tahun', $tahunAnggaran)
                ->where('revenue_masters.is_posted', true)
                ->sum('pendapatan_bpjs.total');
            $incJaminan = DB::table('pendapatan_jaminan')
                ->join('revenue_masters', 'pendapatan_jaminan.revenue_master_id', '=', 'revenue_masters.id')
                ->where('pendapatan_jaminan.tahun', $tahunAnggaran)
                ->where('revenue_masters.is_posted', true)
                ->sum('pendapatan_jaminan.total');
            $incKerja = DB::table('pendapatan_kerjasama')
                ->join('revenue_masters', 'pendapatan_kerjasama.revenue_master_id', '=', 'revenue_masters.id')
                ->where('pendapatan_kerjasama.tahun', $tahunAnggaran)
                ->where('revenue_masters.is_posted', true)
                ->sum('pendapatan_kerjasama.total');
            $incLain = DB::table('pendapatan_lain')
                ->join('revenue_masters', 'pendapatan_lain.revenue_master_id', '=', 'revenue_masters.id')
                ->where('pendapatan_lain.tahun', $tahunAnggaran)
                ->where('revenue_masters.is_posted', true)
                ->sum('pendapatan_lain.total');

            $totalForDist = $incUmum + $incBpjs + $incJaminan + $incKerja + $incLain;

            $distribution = [
                'umum' => $totalForDist > 0 ? round(($incUmum / $totalForDist) * 100, 1) : 0,
                'bpjs' => $totalForDist > 0 ? round(($incBpjs / $totalForDist) * 100, 1) : 0,
                'jaminan' => $totalForDist > 0 ? round(($incJaminan / $totalForDist) * 100, 1) : 0,
                'kerjasama' => $totalForDist > 0 ? round(($incKerja / $totalForDist) * 100, 1) : 0,
                'lainnya' => $totalForDist > 0 ? round(($incLain / $totalForDist) * 100, 1) : 0,
            ];

            return response()->json([
                'summary' => [
                    'totalPendapatanRS' => $totalRS,
                    'totalJasaPelayanan' => $totalPelayanan,
                    'targetPendapatan' => $targetPendapatan,
                    'realisasiPendapatan' => $realisasiNet,
                    'persenCapaian' => $persenCapaian,
                    'targetPengeluaran' => $targetPengeluaran,
                    'realisasiPengeluaran' => $realisasiPengeluaran,
                    'persenCapaianPengeluaran' => $persenCapaianPengeluaran,
                ],
                'distribution' => $distribution,
            ]);

        } catch (\Throwable $e) {
            logger()->error('Dashboard Error', ['msg' => $e->getMessage(), 'line' => $e->getLine()]);
            return response()->json(['error' => 'Dashboard error', 'message' => $e->getMessage()], 500);
        }
    }

    public function chartRooms()
    {
        try {
            $tahunAnggaran = session('tahun_anggaran') ?? now()->year;
            $tables = ['pendapatan_umum', 'pendapatan_bpjs', 'pendapatan_jaminan', 'pendapatan_kerjasama', 'pendapatan_lain'];

            $roomIncome = [];
            // Get all rooms first to ensure labels are complete (optional, but better for consistency)
            $rooms = DB::table('ruangans')->pluck('nama', 'id');

            foreach ($tables as $tbl) {
                $results = DB::table($tbl)
                    ->join('revenue_masters', $tbl . '.revenue_master_id', '=', 'revenue_masters.id')
                    ->where($tbl . '.tahun', $tahunAnggaran)
                    ->where('revenue_masters.is_posted', true)
                    ->select($tbl . '.ruangan_id', DB::raw('SUM(' . $tbl . '.total) as total'))
                    ->groupBy($tbl . '.ruangan_id')
                    ->get();

                foreach ($results as $res) {
                    $roomName = $rooms[$res->ruangan_id] ?? 'Unknown';
                    $roomIncome[$roomName] = ($roomIncome[$roomName] ?? 0) + $res->total;
                }
            }

            // Sort by income DESC
            arsort($roomIncome);

            return response()->json([
                'labels' => array_keys($roomIncome),
                'values' => array_values($roomIncome),
                'year' => $tahunAnggaran
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function chartExpenditure()
    {
        try {
            $tahunAnggaran = session('tahun_anggaran') ?? now()->year;

            $results = DB::table('expenditures')
                ->join('kode_rekening', 'expenditures.kode_rekening_id', '=', 'kode_rekening.id')
                ->whereYear('expenditures.spending_date', $tahunAnggaran)
                ->select('kode_rekening.sumber_data as kategori', DB::raw('SUM(expenditures.gross_value) as total'))
                ->groupBy('kode_rekening.sumber_data')
                ->get();

            $map = [
                'PEGAWAI' => 'Belanja Pegawai',
                'BARANG_JASA' => 'Belanja Barang & Jasa',
                'MODAL' => 'Belanja Modal',
            ];

            $labels = [];
            $values = [];

            foreach ($results as $res) {
                $labels[] = $map[$res->kategori] ?? $res->kategori;
                $values[] = $res->total;
            }

            return response()->json([
                'labels' => $labels,
                'values' => $values,
                'year' => $tahunAnggaran
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /* =========================
       GRAFIK BULANAN (JAN-DES)
    ========================= */
    public function chart7Days()
    {
        $tables = ['pendapatan_umum', 'pendapatan_bpjs', 'pendapatan_jaminan', 'pendapatan_kerjasama', 'pendapatan_lain'];

        // Cari tahun dari data terakhir
        $year = session('tahun_anggaran') ?? now()->year;

        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $labels = [];
        $values = [];
        $valuesPengeluaran = [];

        for ($m = 1; $m <= 12; $m++) {
            $labels[] = $monthNames[$m - 1];

            // 1. PENDAPATAN
            $monthTotal = 0;
            foreach ($tables as $tbl) {
                // Deduct for BPJS and Jaminan in chart too
                $raw = DB::table($tbl)
                    ->join('revenue_masters', $tbl . '.revenue_master_id', '=', 'revenue_masters.id')
                    ->where($tbl . '.tahun', $year)
                    ->whereMonth($tbl . '.tanggal', $m)
                    ->where('revenue_masters.is_posted', true)
                    ->sum($tbl . '.total');

                if ($tbl === 'pendapatan_bpjs' || $tbl === 'pendapatan_jaminan') {
                    $mStart = Carbon::create($year, $m, 1)->toDateString();
                    $mEnd = Carbon::create($year, $m, 1)->endOfMonth()->toDateString();
                    $ded = $this->calculateDeductions($mStart, $mEnd, $tbl, $year);
                    $raw = max(0, $raw - $ded);
                }

                $monthTotal += $raw;
            }
            $values[] = $monthTotal;

            // 2. PENGELUARAN (ECONOMIC)
            $pengTotal = DB::table('expenditures')
                ->whereYear('spending_date', $year)
                ->whereMonth('spending_date', $m)
                ->sum('gross_value');
            $valuesPengeluaran[] = $pengTotal;
        }

        return response()->json([
            'labels' => $labels,
            'values' => $values,
            'valuesPengeluaran' => $valuesPengeluaran,
            'year' => $year
        ]);
    }

    private function calculateDeductions($startDate, $endDate, $table, $tahun)
    {
        $kategori = ($table === 'pendapatan_bpjs') ? 'BPJS' : 'JAMINAN';

        return DB::table('penyesuaian_pendapatans')
            ->where('kategori', $kategori)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->where('tahun', $tahun)
            ->sum(DB::raw('IFNULL(potongan, 0) + IFNULL(administrasi_bank, 0)'));
    }
}
