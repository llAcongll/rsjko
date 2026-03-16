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
        $user = auth()->user();

        // Dynamic Guard for high-level pages
        $pagePermissions = [
            'dashboard' => 'DASHBOARD_VIEW',
            'users' => 'USER_VIEW',
            'ruangan' => 'RUANGAN_VIEW',
            'perusahaan' => 'PERUSAHAAN_VIEW',
            'mou' => 'MOU_VIEW',
            'penanda_tangan' => 'PENANDATANGAN_VIEW',
            'piutang' => 'PIUTANG_VIEW',
            'penyesuaian' => 'PENYESUAIAN_VIEW',
        ];

        if (array_key_exists($page, $pagePermissions)) {
            if (!$user->hasPermission($pagePermissions[$page])) {
                abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
            }
        }

        return match ($page) {

            'dashboard' => view('dashboard.pages.dashboard'),

            'users' => view('dashboard.pages.users', [
                'users' => User::orderBy(request('sort_by', 'username'), request('sort_dir', 'asc'))->get()
            ]),

            'pendapatan' => match ($param) {
                    'UMUM' => $user->hasPermission('PENDAPATAN_UMUM_VIEW') ? view('dashboard.pages.pendapatan.umum') : abort(403),
                    'BPJS' => $user->hasPermission('PENDAPATAN_BPJS_VIEW') ? view('dashboard.pages.pendapatan.bpjs') : abort(403),
                    'JAMINAN' => $user->hasPermission('PENDAPATAN_JAMINAN_VIEW') ? view('dashboard.pages.pendapatan.jaminan') : abort(403),
                    'KERJASAMA' => $user->hasPermission('PENDAPATAN_KERJA_VIEW') ? view('dashboard.pages.pendapatan.kerjasama') : abort(403),
                    'LAIN' => $user->hasPermission('PENDAPATAN_LAIN_VIEW') ? view('dashboard.pages.pendapatan.lainlain') : abort(403),
                    'ANGGARAN' => $user->hasPermission('ANGGARAN_PENDAPATAN_VIEW') ? view('dashboard.pages.pendapatan.anggaran') : abort(403),
                    'BKU' => $user->hasPermission('BKU_PENDAPATAN_VIEW') ? view('dashboard.pages.pendapatan.bku') : abort(403),
                    'rekening-koran' => $user->hasPermission('REKKOR_VIEW') ? view('dashboard.pages.rekening') : abort(403),
                    default => abort(404),
                },

            'laporan' => match ($param) {
                    'PENDAPATAN' => $user->hasPermission('LAP_PENDAPATAN_VIEW') ? view('dashboard.pages.laporan.pendapatan') : abort(403),
                    'PENGELUARAN' => $user->hasPermission('LAP_PENGELUARAN_VIEW') ? view('dashboard.pages.laporan.pengeluaran') : abort(403),
                    'REKON' => $user->hasPermission('LAP_REKON_VIEW') ? view('dashboard.pages.laporan.rekon') : abort(403),
                    'PIUTANG' => $user->hasPermission('LAP_PIUTANG_VIEW') ? view('dashboard.pages.laporan.piutang') : abort(403),
                    'LRA', 'ANGGARAN' => $user->hasPermission('LAP_LRA_VIEW') ? view('dashboard.pages.laporan.anggaran') : abort(403),
                    'LO' => $user->hasPermission('LAP_LO_VIEW') ? view('dashboard.pages.laporan.lo') : abort(403),
                    'NERACA' => $user->hasPermission('LAP_NERACA_VIEW') ? view('dashboard.pages.laporan.neraca') : abort(403),
                    'LAK' => $user->hasPermission('LAP_LAK_VIEW') ? view('dashboard.pages.laporan.lak') : abort(403),
                    'LPE' => $user->hasPermission('LAP_LPE_VIEW') ? view('dashboard.pages.laporan.lpe') : abort(403),
                    'LPSAL' => $user->hasPermission('LAP_LPSAL_VIEW') ? view('dashboard.pages.laporan.lpsal') : abort(403),
                    'CALK' => $user->hasPermission('LAP_CALK_VIEW') ? view('dashboard.pages.laporan.calk') : abort(403),
                    'RKA' => $user->hasPermission('LAP_RKA_VIEW') ? view('dashboard.pages.laporan.rka') : abort(403),
                    'RBA' => $user->hasPermission('LAP_RBA_VIEW') ? view('dashboard.pages.laporan.rba') : abort(403),
                    'DPA' => $user->hasPermission('LAP_DPA_VIEW') ? view('dashboard.pages.laporan.dpa') : abort(403),
                    default => abort(404),
                },

            'rekening' => ($user->hasPermission('KODE_REKENING_PENDAPATAN_VIEW') || $user->hasPermission('KODE_REKENING_PENGELUARAN_VIEW')) ? view('dashboard.pages.rekening') : abort(403),

            'ruangan' => view('dashboard.pages.ruangan'),
            'perusahaan' => view('dashboard.pages.perusahaan'),
            'mou' => view('dashboard.pages.mou'),
            'penanda_tangan' => view('dashboard.pages.penanda_tangan'),
            'piutang' => view('dashboard.pages.piutang'),
            'penyesuaian' => view('dashboard.pages.penyesuaian'),

            'master' => match ($param) {
                    'kode-rekening' => (
                        (request('category') === 'PENGELUARAN' && $user->hasPermission('KODE_REKENING_PENGELUARAN_VIEW')) ||
                        (request('category') !== 'PENGELUARAN' && $user->hasPermission('KODE_REKENING_PENDAPATAN_VIEW'))
                    ) ? (
                        request('category') === 'PENGELUARAN'
                        ? view('dashboard.master.kode-rekening.expenditure')
                        : view('dashboard.master.kode-rekening.index')
                    ) : abort(403),
                    'kode-rekening-anggaran' => (
                        (request('category') === 'PENGELUARAN' && $user->hasPermission('ANGGARAN_PENGELUARAN_VIEW')) ||
                        (request('category') !== 'PENGELUARAN' && $user->hasPermission('ANGGARAN_PENDAPATAN_VIEW'))
                    ) ? (
                        request('category') === 'PENGELUARAN'
                        ? view('dashboard.pages.pengeluaran.anggaran')
                        : view('dashboard.pages.pendapatan.anggaran')
                    ) : abort(403),
                    'logs' => $user->hasPermission('LOG_VIEW') ? view('dashboard.pages.master.logs') : abort(403),
                    default => abort(404),
                },

            'pengeluaran' => match ($param) {
                    'PEGAWAI', 'BARANG_JASA', 'MODAL' => $user->hasPermission('BELANJA_VIEW') ? view('dashboard.pages.pengeluaran.index', ['param' => $param]) : abort(403),
                    'ANGGARAN' => $user->hasPermission('ANGGARAN_PENGELUARAN_VIEW') ? view('dashboard.pages.pengeluaran.anggaran') : abort(403),
                    'disbursement' => ($user->hasPermission('SPP_VIEW') || $user->hasPermission('SPM_VIEW') || $user->hasPermission('SP2D_VIEW')) ? view('dashboard.pages.pengeluaran.disbursement') : abort(403),
                    'ledger' => $user->hasPermission('BKU_PENGELUARAN_VIEW') ? view('dashboard.pages.pengeluaran.ledger') : abort(403),
                    'rekening-koran' => $user->hasPermission('REK_KORAN_PENG_VIEW') ? view('dashboard.pages.pengeluaran.rekening-koran') : abort(403),
                    'saldo' => $user->hasPermission('BELANJA_VIEW') ? view('dashboard.pages.pengeluaran.saldo') : abort(403),
                    default => abort(404),
                },

            'pengesahan' => match ($param) {
                    'SP3BP', 'sp3bp' => $user->hasPermission('SP3BP_VIEW') ? view('dashboard.pages.pengesahan.sp3bp') : abort(403),
                    'SPTJB', 'sptjb' => $user->hasPermission('SPTJB_VIEW') ? view('dashboard.pages.pengesahan.sptjb') : abort(403),
                    'LRKB', 'lrkb' => $user->hasPermission('LRKB_VIEW') ? view('dashboard.pages.pengesahan.lrkb') : abort(403),
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
            $revenueSubqueries = collect($tables)->map(function ($tbl) use ($tahunAnggaran) {
                return DB::table($tbl)
                    ->join('revenue_masters', $tbl . '.revenue_master_id', '=', 'revenue_masters.id')
                    ->where($tbl . '.tahun', $tahunAnggaran)
                    ->where('revenue_masters.is_posted', true)
                    ->select(
                        DB::raw("'$tbl' as source_table"),
                        DB::raw('(' . $tbl . '.rs_tindakan + ' . $tbl . '.rs_obat) as rs'),
                        DB::raw('(' . $tbl . '.pelayanan_tindakan + ' . $tbl . '.pelayanan_obat) as pelayanan'),
                        DB::raw($tbl . '.total as total_row')
                    );
            });

            $unionedRevenue = DB::table(function ($query) use ($revenueSubqueries) {
                $first = $revenueSubqueries->shift();
                $query->from($first);
                foreach ($revenueSubqueries as $sub) {
                    $query->unionAll($sub);
                }
            }, 'combined_revenues');

            $allSums = $unionedRevenue->select(
                DB::raw('SUM(rs) as total_rs'),
                DB::raw('SUM(pelayanan) as total_pelayanan'),
                DB::raw("SUM(CASE WHEN source_table = 'pendapatan_umum' THEN total_row ELSE 0 END) as inc_umum"),
                DB::raw("SUM(CASE WHEN source_table = 'pendapatan_bpjs' THEN total_row ELSE 0 END) as inc_bpjs"),
                DB::raw("SUM(CASE WHEN source_table = 'pendapatan_jaminan' THEN total_row ELSE 0 END) as inc_jaminan"),
                DB::raw("SUM(CASE WHEN source_table = 'pendapatan_kerjasama' THEN total_row ELSE 0 END) as inc_kerja"),
                DB::raw("SUM(CASE WHEN source_table = 'pendapatan_lain' THEN total_row ELSE 0 END) as inc_lain")
            )->first();

            $totalRS = $allSums->total_rs ?? 0;
            $totalPelayanan = $allSums->total_pelayanan ?? 0;

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
            $incUmum = $allSums->inc_umum ?? 0;
            $incBpjs = $allSums->inc_bpjs ?? 0;
            $incJaminan = $allSums->inc_jaminan ?? 0;
            $incKerja = $allSums->inc_kerja ?? 0;
            $incLain = $allSums->inc_lain ?? 0;

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

            $roomSubqueries = collect($tables)->map(function ($tbl) use ($tahunAnggaran) {
                return DB::table($tbl)
                    ->join('revenue_masters', $tbl . '.revenue_master_id', '=', 'revenue_masters.id')
                    ->where($tbl . '.tahun', $tahunAnggaran)
                    ->where('revenue_masters.is_posted', true)
                    ->select($tbl . '.ruangan_id', DB::raw('SUM(' . $tbl . '.total) as total'))
                    ->groupBy($tbl . '.ruangan_id');
            });

            $unionedRooms = DB::table(function ($query) use ($roomSubqueries) {
                $first = $roomSubqueries->shift();
                $query->from($first);
                foreach ($roomSubqueries as $sub) {
                    $query->unionAll($sub);
                }
            }, 'combined_rooms')
            ->select('ruangan_id', DB::raw('SUM(total) as total'))
            ->groupBy('ruangan_id')
            ->get();

            foreach ($unionedRooms as $res) {
                $roomName = $rooms[$res->ruangan_id] ?? 'Unknown';
                $roomIncome[$roomName] = ($roomIncome[$roomName] ?? 0) + $res->total;
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
        $year = session('tahun_anggaran') ?? now()->year;

        // 1. Fetch all Income grouped by month and table
        $incomeSubqueries = collect($tables)->map(function ($tbl) use ($year) {
            return DB::table($tbl)
                ->join('revenue_masters', $tbl . '.revenue_master_id', '=', 'revenue_masters.id')
                ->where($tbl . '.tahun', $year)
                ->where('revenue_masters.is_posted', true)
                ->select(
                    DB::raw("MONTH($tbl.tanggal) as bulan"),
                    DB::raw("'$tbl' as source_table"),
                    DB::raw("SUM($tbl.total) as total_inc")
                )
                ->groupBy(DB::raw("MONTH($tbl.tanggal)"), DB::raw("'$tbl'"));
        });

        $monthlyIncomeRaw = DB::table(function ($query) use ($incomeSubqueries) {
            $first = $incomeSubqueries->shift();
            $query->from($first);
            foreach ($incomeSubqueries as $sub) {
                $query->unionAll($sub);
            }
        }, 'combined_monthly_inc')
        ->select('bulan', 'source_table', 'total_inc')
        ->get();

        // 2. Fetch all Deductions grouped by month and category
        $monthlyDeductions = DB::table('penyesuaian_pendapatans')
            ->where('tahun', $year)
            ->select(
                DB::raw("MONTH(tanggal) as bulan"),
                'kategori',
                DB::raw('SUM(IFNULL(potongan, 0) + IFNULL(administrasi_bank, 0)) as total_ded')
            )
            ->groupBy(DB::raw("MONTH(tanggal)"), 'kategori')
            ->get()
            ->groupBy('bulan');

        // 3. Fetch all Expenditures grouped by month
        $monthlyExpenditures = DB::table('expenditures')
            ->whereYear('spending_date', $year)
            ->select(
                DB::raw("MONTH(spending_date) as bulan"),
                DB::raw('SUM(gross_value) as total_exp')
            )
            ->groupBy(DB::raw("MONTH(spending_date)"))
            ->pluck('total_exp', 'bulan');

        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $labels = [];
        $values = [];
        $valuesPengeluaran = [];

        for ($m = 1; $m <= 12; $m++) {
            $labels[] = $monthNames[$m - 1];

            // Calculate Income for this month from union result
            $monthTotal = 0;
            $monthRows = $monthlyIncomeRaw->where('bulan', $m);
            
            foreach ($tables as $tbl) {
                $raw = $monthRows->where('source_table', $tbl)->sum('total_inc');

                if ($tbl === 'pendapatan_bpjs' || $tbl === 'pendapatan_jaminan') {
                    $kat = ($tbl === 'pendapatan_bpjs') ? 'BPJS' : 'JAMINAN';
                    $ded = $monthlyDeductions->get($m)?->where('kategori', $kat)->sum('total_ded') ?? 0;
                    $raw = max(0, $raw - $ded);
                }
                $monthTotal += $raw;
            }

            $values[] = $monthTotal;
            $valuesPengeluaran[] = $monthlyExpenditures->get($m) ?? 0;
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





