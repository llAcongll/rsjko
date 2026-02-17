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

            'users' => Auth::user()->isAdmin()
            ? view('dashboard.pages.users', [
                'users' => User::orderBy('username')->get()
            ])
            : abort(403),

            'pendapatan' => match ($param) {
                    'UMUM' => Auth::user()->hasPermission('PENDAPATAN_UMUM_VIEW') ? view('dashboard.pages.pendapatan.umum') : abort(403),
                    'BPJS' => Auth::user()->hasPermission('PENDAPATAN_BPJS_VIEW') ? view('dashboard.pages.pendapatan.bpjs') : abort(403),
                    'JAMINAN' => Auth::user()->hasPermission('PENDAPATAN_JAMINAN_VIEW') ? view('dashboard.pages.pendapatan.jaminan') : abort(403),
                    'KERJASAMA' => Auth::user()->hasPermission('PENDAPATAN_KERJA_VIEW') ? view('dashboard.pages.pendapatan.kerjasama') : abort(403),
                    'LAIN' => Auth::user()->hasPermission('PENDAPATAN_LAIN_VIEW') ? view('dashboard.pages.pendapatan.lainlain') : abort(403),
                    'ANGGARAN' => Auth::user()->hasPermission('KODE_REKENING_VIEW') ? view('dashboard.pages.pendapatan.anggaran') : abort(403),
                    default => abort(404),
                },

            'laporan' => match ($param) {
                    'PENDAPATAN' => (Auth::user()->hasPermission('LAPORAN_PENDAPATAN') || Auth::user()->hasPermission('LAPORAN_VIEW')) ? view('dashboard.pages.laporan.pendapatan') : abort(403),
                    'REKON' => (Auth::user()->hasPermission('LAPORAN_REKON') || Auth::user()->hasPermission('LAPORAN_VIEW')) ? view('dashboard.pages.laporan.rekon') : abort(403),
                    'PIUTANG' => (Auth::user()->hasPermission('LAPORAN_PIUTANG') || Auth::user()->hasPermission('LAPORAN_VIEW')) ? view('dashboard.pages.laporan.piutang') : abort(403),
                    'MOU' => (Auth::user()->hasPermission('LAPORAN_MOU') || Auth::user()->hasPermission('LAPORAN_VIEW')) ? view('dashboard.pages.laporan.mou') : abort(403),
                    'ANGGARAN' => (Auth::user()->hasPermission('LAPORAN_ANGGARAN') || Auth::user()->hasPermission('LAPORAN_VIEW')) ? view('dashboard.pages.laporan.anggaran') : abort(403),
                    default => (Auth::user()->hasPermission('LAPORAN_PENDAPATAN') || Auth::user()->hasPermission('LAPORAN_VIEW')) ? view('dashboard.pages.laporan.pendapatan') : abort(403),
                },
            'rekening' => Auth::user()->hasPermission('REKENING_VIEW') ? view('dashboard.pages.rekening') : abort(403),

            'ruangan' => Auth::user()->hasPermission('MASTER_VIEW') ? view('dashboard.pages.ruangan') : abort(403),
            'perusahaan' => Auth::user()->hasPermission('MASTER_VIEW') ? view('dashboard.pages.perusahaan') : abort(403),
            'mou' => Auth::user()->hasPermission('MASTER_VIEW') ? view('dashboard.pages.mou') : abort(403),
            'piutang' => Auth::user()->hasPermission('PIUTANG_VIEW') ? view('dashboard.pages.piutang') : abort(403),
            'penyesuaian' => Auth::user()->hasPermission('PENYESUAIAN_VIEW') ? view('dashboard.pages.penyesuaian') : abort(403),

            'master' => match ($param) {
                    'kode-rekening' => Auth::user()->hasPermission('KODE_REKENING_VIEW') ? view('dashboard.master.kode-rekening.index') : abort(403),
                    'kode-rekening-anggaran' => Auth::user()->hasPermission('KODE_REKENING_VIEW') ? view('dashboard.pages.pendapatan.anggaran') : abort(403),
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
            $tables = ['pendapatan_umum', 'pendapatan_bpjs', 'pendapatan_jaminan', 'pendapatan_kerjasama', 'pendapatan_lain'];

            /* 🔑 TANGGAL DASHBOARD = DATA TERAKHIR DARI SEMUA TABEL */
            $dashboardDate = null;
            $tahunAnggaran = session('tahun_anggaran');

            foreach ($tables as $tbl) {
                $maxTbl = DB::table($tbl)
                    ->where('tahun', $tahunAnggaran)
                    ->max('tanggal');
                if ($maxTbl && (!$dashboardDate || $maxTbl > $dashboardDate)) {
                    $dashboardDate = $maxTbl;
                }
            }

            if (!$dashboardDate) {
                return response()->json([
                    'summary' => [
                        'todayIncome' => 0,
                        'monthIncome' => 0,
                        'todayTransaction' => 0,
                        'activeRoom' => 0,
                        'todayGrowth' => 0,
                    ],
                    'distribution' => [
                        'umum' => 100,
                        'bpjs' => 0,
                        'jaminan' => 0,
                        'lainnya' => 0,
                    ],
                    'todaySummary' => [
                        'totalTransaction' => 0,
                        'topIncomeType' => 'UMUM',
                        'topRoom' => '-',
                        'dominantPatient' => 'UMUM',
                    ],
                ]);
            }

            $dashboardDate = Carbon::parse($dashboardDate)->toDateString();
            $monthStart = Carbon::parse($dashboardDate)->startOfMonth()->toDateString();

            /* =========================
               SUMMARY TOTALS
            ========================= */
            $todayIncome = 0;
            $monthIncome = 0;
            $todayTransaction = 0;

            foreach ($tables as $tbl) {
                $todayIncome += DB::table($tbl)
                    ->whereDate('tanggal', $dashboardDate)
                    ->where('tahun', $tahunAnggaran)
                    ->sum('total');
                $monthIncome += DB::table($tbl)
                    ->whereBetween('tanggal', [$monthStart, $dashboardDate])
                    ->where('tahun', $tahunAnggaran)
                    ->sum('total');
                $todayTransaction += DB::table($tbl)
                    ->whereDate('tanggal', $dashboardDate)
                    ->where('tahun', $tahunAnggaran)
                    ->count();
            }

            $activeRoom = DB::table('ruangans')->count();

            /* =========================
               TODAY GROWTH (vs hari sebelumnya)
            ========================= */
            $yesterdayDate = Carbon::parse($dashboardDate)->subDay()->toDateString();
            $yesterdayIncome = 0;
            foreach ($tables as $tbl) {
                $yesterdayIncome += DB::table($tbl)
                    ->whereDate('tanggal', $yesterdayDate)
                    ->where('tahun', $tahunAnggaran)
                    ->sum('total');
            }

            $todayGrowth = 0;
            if ($yesterdayIncome > 0) {
                $todayGrowth = round((($todayIncome - $yesterdayIncome) / $yesterdayIncome) * 100, 1);
            }

            /* =========================
               TOP ROOM (Across all tables for today)
            ========================= */
            $roomCounts = [];
            foreach ($tables as $tbl) {
                $results = DB::table($tbl)
                    ->join('ruangans', "$tbl.ruangan_id", '=', 'ruangans.id')
                    ->select('ruangans.nama', DB::raw('COUNT(*) as total'))
                    ->whereDate("$tbl.tanggal", $dashboardDate)
                    ->where("$tbl.tahun", $tahunAnggaran)
                    ->groupBy('ruangans.nama')
                    ->get();
                foreach ($results as $r) {
                    $roomCounts[$r->nama] = ($roomCounts[$r->nama] ?? 0) + $r->total;
                }
            }
            arsort($roomCounts);
            $topRoom = key($roomCounts) ?? '-';

            /* =========================
               DISTRIBUSI PASIEN (DINAMIS - MONTHLY)
            ========================= */
            $incUmum = DB::table('pendapatan_umum')
                ->whereBetween('tanggal', [$monthStart, $dashboardDate])
                ->where('tahun', $tahunAnggaran)
                ->sum('total');

            // BPJS Deduction
            $bpjsRaw = DB::table('pendapatan_bpjs')
                ->whereBetween('tanggal', [$monthStart, $dashboardDate])
                ->where('tahun', $tahunAnggaran)
                ->sum('total');
            $bpjsDed = $this->calculateDeductions($monthStart, $dashboardDate, 'pendapatan_bpjs', $tahunAnggaran);
            $incBpjs = max(0, $bpjsRaw - $bpjsDed);

            // Jaminan Deduction
            $jamRaw = DB::table('pendapatan_jaminan')
                ->whereBetween('tanggal', [$monthStart, $dashboardDate])
                ->where('tahun', $tahunAnggaran)
                ->sum('total');
            $jamDed = $this->calculateDeductions($monthStart, $dashboardDate, 'pendapatan_jaminan', $tahunAnggaran);
            $incJaminan = max(0, $jamRaw - $jamDed);

            $incKerja = DB::table('pendapatan_kerjasama')
                ->whereBetween('tanggal', [$monthStart, $dashboardDate])
                ->where('tahun', $tahunAnggaran)
                ->sum('total');
            $incLain = DB::table('pendapatan_lain')
                ->whereBetween('tanggal', [$monthStart, $dashboardDate])
                ->where('tahun', $tahunAnggaran)
                ->sum('total');

            // Today Deductions for Today Income
            $todayBpjsDed = $this->calculateDeductions($dashboardDate, $dashboardDate, 'pendapatan_bpjs', $tahunAnggaran);
            $todayJamDed = $this->calculateDeductions($dashboardDate, $dashboardDate, 'pendapatan_jaminan', $tahunAnggaran);
            $todayIncome = max(0, $todayIncome - ($todayBpjsDed + $todayJamDed));

            // Month Income is sum of nets
            $monthIncome = $incUmum + $incBpjs + $incJaminan + $incKerja + $incLain;

            // Updated Growth logic (Yesterday Net)
            $yesterdayBpjsDed = $this->calculateDeductions($yesterdayDate, $yesterdayDate, 'pendapatan_bpjs', $tahunAnggaran);
            $yesterdayJamDed = $this->calculateDeductions($yesterdayDate, $yesterdayDate, 'pendapatan_jaminan', $tahunAnggaran);
            $yesterdayIncome = max(0, $yesterdayIncome - ($yesterdayBpjsDed + $yesterdayJamDed));

            $todayGrowth = 0;
            if ($yesterdayIncome > 0) {
                $todayGrowth = round((($todayIncome - $yesterdayIncome) / $yesterdayIncome) * 100, 1);
            }

            $totalAll = $monthIncome;

            $distribution = [
                'umum' => $totalAll > 0 ? round(($incUmum / $totalAll) * 100, 1) : 0,
                'bpjs' => $totalAll > 0 ? round(($incBpjs / $totalAll) * 100, 1) : 0,
                'jaminan' => $totalAll > 0 ? round(($incJaminan / $totalAll) * 100, 1) : 0,
                'kerjasama' => $totalAll > 0 ? round(($incKerja / $totalAll) * 100, 1) : 0,
                'lainnya' => $totalAll > 0 ? round(($incLain / $totalAll) * 100, 1) : 0,
            ];

            if ($totalAll == 0)
                $distribution['umum'] = 100;

            /* =========================
               TOP INCOME TYPE
            ========================= */
            $incomeTypes = [
                'UMUM' => $incUmum,
                'BPJS' => $incBpjs,
                'JAMINAN' => $incJaminan,
                'KERJASAMA' => $incKerja,
                'LAIN' => $incLain
            ];
            arsort($incomeTypes);
            $topIncomeType = key($incomeTypes) ?? 'UMUM';

            return response()->json([
                'summary' => [
                    'todayIncome' => $todayIncome,
                    'monthIncome' => $monthIncome,
                    'todayTransaction' => $todayTransaction,
                    'activeRoom' => $activeRoom,
                    'todayGrowth' => $todayGrowth,
                ],
                'distribution' => $distribution,
                'todaySummary' => [
                    'totalTransaction' => $todayTransaction,
                    'topIncomeType' => $topIncomeType,
                    'topRoom' => $topRoom,
                    'dominantPatient' => $topIncomeType,
                ],
            ]);

        } catch (\Throwable $e) {
            logger()->error('Dashboard Error', ['msg' => $e->getMessage(), 'line' => $e->getLine()]);
            return response()->json(['error' => 'Dashboard error', 'message' => $e->getMessage()], 500);
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

        for ($m = 1; $m <= 12; $m++) {
            $labels[] = $monthNames[$m - 1];
            $monthTotal = 0;
            foreach ($tables as $tbl) {
                // Deduct for BPJS and Jaminan in chart too
                $raw = DB::table($tbl)
                    ->where('tahun', $year)
                    ->whereMonth('tanggal', $m)
                    ->sum('total');

                if ($tbl === 'pendapatan_bpjs' || $tbl === 'pendapatan_jaminan') {
                    $mStart = Carbon::create($year, $m, 1)->toDateString();
                    $mEnd = Carbon::create($year, $m, 1)->endOfMonth()->toDateString();
                    $ded = $this->calculateDeductions($mStart, $mEnd, $tbl, $year);
                    $raw = max(0, $raw - $ded);
                }

                $monthTotal += $raw;
            }
            $values[] = $monthTotal;
        }

        return response()->json([
            'labels' => $labels,
            'values' => $values,
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
