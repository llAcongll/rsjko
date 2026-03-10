<?php

namespace App\Http\Controllers;

use App\Models\PengesahanPeriode;
use App\Models\SP3BP;
use App\Models\SP3BPBelanja;
use App\Models\SP3BPPendapatan;
use App\Models\SP3BPRekonsiliasi;
use App\Models\KodeRekening;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class SP3BPController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        abort_unless(auth()->user()->hasPermission('SP3BP_VIEW'), 403);
        $periodes = PengesahanPeriode::with('sp3bp')
            ->orderBy('id', 'asc')
            ->get();
        return response()->json($periodes);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('SP3BP_GENERATE'), 403);
        $request->validate([
            'triwulan' => 'nullable|integer|between:1,4',
            'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'required|integer',
        ]);

        $query = PengesahanPeriode::where('tahun', $request->tahun);
        if ($request->triwulan) {
            $query->where('triwulan', $request->triwulan);
        } else {
            $query->where('bulan', $request->bulan);
        }

        if ($query->exists()) {
            return response()->json(['error' => 'Periode ini sudah ada'], 422);
        }

        $periode = PengesahanPeriode::create([
            'triwulan' => $request->triwulan,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        return response()->json($periode);
    }

    public function show($id)
    {
        abort_unless(auth()->user()->hasPermission('SP3BP_VIEW'), 403);
        $sp3bp = SP3BP::with(['periode', 'detailPendapatan', 'detailBelanja', 'rekonsiliasi'])
            ->where('periode_id', $id)
            ->first();

        if (!$sp3bp) {
            $periode = PengesahanPeriode::find($id);
            return response()->json([
                'success' => false,
                'message' => 'Belum di-generate',
                'periode' => $periode
            ]);
        }

        return response()->json(array_merge($sp3bp->toArray(), ['success' => true]));
    }

    public function generate($id)
    {
        abort_unless(auth()->user()->hasPermission('SP3BP_GENERATE'), 403);
        $periode = PengesahanPeriode::findOrFail($id);

        if ($periode->status === 'terkunci') {
            return response()->json(['error' => 'Periode sudah terkunci'], 422);
        }

        $year = $periode->tahun;
        $t = $periode->triwulan;

        // 0. Audit Check: Check if LRKB is valid
        $lrkb = \App\Models\LRKB::where('tahun', $year)->where('triwulan', $t)->first();
        if (!$lrkb || $lrkb->status !== 'valid') {
            return response()->json(['error' => 'SP3BP tidak dapat dibuat! Lakukan rekonsiliasi kas (LRKB) dan pastikan berstatus VALID terlebih dahulu.'], 422);
        }

        if ($t) {
            // Quarterly Logic
            $startMonth = ($t - 1) * 3 + 1;
            $endMonth = $t * 3;
            $startDate = Carbon::create($year, $startMonth, 1)->toDateString();
            $endDate = Carbon::create($year, $endMonth, 1)->endOfMonth()->toDateString();

            // For BKU Reconciliation, we use the balance at the end of the quarter
            $bkuLastMonth = $endMonth;
        } else {
            // Fallback to Monthly
            $month = $periode->bulan;
            $startDate = Carbon::create($year, $month, 1)->toDateString();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
            $bkuLastMonth = $month;
        }

        // 1. Get Saldo Awal
        $saldoAwal = 0;
        if ($t) {
            $prevT = $t == 1 ? 4 : $t - 1;
            $prevYear = $t == 1 ? $year - 1 : $year;
            $prevPeriode = PengesahanPeriode::where('tahun', $prevYear)->where('triwulan', $prevT)->where('status', '!=', 'draft')->first();
        } else {
            $prevMonth = $month == 1 ? 12 : $month - 1;
            $prevYear = $month == 1 ? $year - 1 : $year;
            $prevPeriode = PengesahanPeriode::where('tahun', $prevYear)->where('bulan', $prevMonth)->where('status', '!=', 'draft')->first();
        }

        if ($prevPeriode && $prevPeriode->sp3bp) {
            $saldoAwal = $prevPeriode->sp3bp->saldo_akhir;
        }

        // 2. Aggregate Revenue
        $revenueItems = $this->aggregateRevenue($startDate, $endDate, $year);
        $totalPendapatan = collect($revenueItems)->sum('jumlah');

        // 3. Aggregate Expenditures
        $expenditureItems = $this->aggregateExpenditure($startDate, $endDate);
        $totalBelanja = collect($expenditureItems)->sum('jumlah');

        // 4. Calculate Saldo Akhir
        $saldoAkhir = $saldoAwal + $totalPendapatan - $totalBelanja;

        // 5. Get reconciliation data (BKU summary from the end of period)
        // Expenditure BKU
        $bkuExp = $this->reportService->getBkuData($year, $bkuLastMonth);
        $summaryExp = $bkuExp['summary'] ?? [];
        $saldoBank = $summaryExp['final_bank'] ?? 0;
        $saldoTunaiExp = $summaryExp['final_tunai'] ?? 0;

        // Income BKU
        $incomeCashBookService = app(\App\Services\IncomeCashBookService::class);
        $bkuInc = $incomeCashBookService->getLedgerData($year, $bkuLastMonth);
        $saldoTunaiInc = $bkuInc['summary']['final_saldo'] ?? 0;

        $saldoTunai = $saldoTunaiExp + $saldoTunaiInc;
        $saldoBuku = $saldoBank + $saldoTunai;
        $selisih = round($saldoAkhir - $saldoBuku, 2);

        // EXTRA: Calculate Physical In/Out for separation in view
        $bankIn = DB::table('bank_account_ledgers')->whereBetween('date', [$startDate, $endDate])->sum('debit');
        $bankOut = DB::table('bank_account_ledgers')->whereBetween('date', [$startDate, $endDate])->sum('credit');
        $tunaiIn = DB::table('treasurer_cash')->whereBetween('date', [$startDate, $endDate])->sum('debit');
        $tunaiOut = DB::table('treasurer_cash')->whereBetween('date', [$startDate, $endDate])->sum('credit');

        DB::beginTransaction();
        try {
            $sp3bp = SP3BP::updateOrCreate(
                ['periode_id' => $id],
                [
                    'saldo_awal' => $saldoAwal,
                    'pendapatan' => $totalPendapatan,
                    'belanja' => $totalBelanja,
                    'saldo_akhir' => $saldoAkhir,
                    'selisih' => $selisih,
                    'status' => 'draft'
                ]
            );

            // Sync Details
            $sp3bp->detailPendapatan()->delete();
            $sp3bp->detailPendapatan()->createMany($revenueItems);

            $sp3bp->detailBelanja()->delete();
            $sp3bp->detailBelanja()->createMany($expenditureItems);

            $sp3bp->rekonsiliasi()->updateOrCreate(
                ['sp3bp_id' => $sp3bp->id],
                [
                    'bank_masuk' => $bankIn,
                    'bank_keluar' => $bankOut,
                    'tunai_masuk' => $tunaiIn,
                    'tunai_keluar' => $tunaiOut,
                    'saldo_bank' => $saldoBank,
                    'saldo_tunai' => $saldoTunai,
                    'saldo_buku' => $saldoBuku,
                    'selisih' => $selisih
                ]
            );

            DB::commit();
            return response()->json($sp3bp->load(['detailPendapatan', 'detailBelanja', 'rekonsiliasi']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function aggregateRevenue($start, $end, $year)
    {
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

        $items = [];
        foreach ($categories as $key => $meta) {
            $val = $this->calculateRealisasiRevenue($key, $year, $start, $end);
            if ($val > 0) {
                // Group by kode if multiple categories share same code
                if (isset($items[$meta['kode']])) {
                    $items[$meta['kode']]['jumlah'] += $val;
                } else {
                    $items[$meta['kode']] = [
                        'kode_rekening' => $meta['kode'],
                        'uraian' => $meta['nama'],
                        'jumlah' => $val
                    ];
                }
            }
        }
        return array_values($items);
    }

    private function calculateRealisasiRevenue($sumberData, $tahun, $startDate, $endDate)
    {
        switch ($sumberData) {
            case 'PASIEN_UMUM':
                return DB::table('pendapatan_umum as t')
                    ->join('revenue_masters as rm', 't.revenue_master_id', '=', 'rm.id')
                    ->where('rm.tahun', $tahun)
                    ->where('rm.is_posted', 1)
                    ->whereBetween('t.tanggal', [$startDate, $endDate])
                    ->sum('t.total');
            case 'BPJS_JAMINAN':
                $bpjs = DB::table('pendapatan_bpjs as t')
                    ->join('revenue_masters as rm', 't.revenue_master_id', '=', 'rm.id')
                    ->where('rm.tahun', $tahun)
                    ->where('rm.is_posted', 1)
                    ->whereBetween('t.tanggal', [$startDate, $endDate])
                    ->sum('t.total');
                $jam = DB::table('pendapatan_jaminan as t')
                    ->join('revenue_masters as rm', 't.revenue_master_id', '=', 'rm.id')
                    ->where('rm.tahun', $tahun)
                    ->where('rm.is_posted', 1)
                    ->whereBetween('t.tanggal', [$startDate, $endDate])
                    ->sum('t.total');
                $ded = DB::table('penyesuaian_pendapatans')->whereIn('kategori', ['BPJS', 'JAMINAN'])->whereBetween('tanggal', [$startDate, $endDate])->where('tahun', $tahun)->sum(DB::raw('IFNULL(potongan, 0) + IFNULL(administrasi_bank, 0)'));
                return ($bpjs + $jam) - $ded;
            case 'KERJASAMA':
                return DB::table('pendapatan_kerjasama as t')
                    ->join('revenue_masters as rm', 't.revenue_master_id', '=', 'rm.id')
                    ->where('rm.tahun', $tahun)
                    ->where('rm.is_posted', 1)
                    ->whereBetween('t.tanggal', [$startDate, $endDate])
                    ->sum('t.total');
            case 'PKL':
                return DB::table('pendapatan_lain as t')
                    ->join('revenue_masters as rm', 't.revenue_master_id', '=', 'rm.id')
                    ->where('rm.tahun', $tahun)
                    ->where('rm.is_posted', 1)
                    ->whereBetween('t.tanggal', [$startDate, $endDate])
                    ->where(fn($q) => $q->where('t.transaksi', 'like', '%PKL%')->orWhere('t.transaksi', 'like', '%Praktek Kerja Lapangan%'))
                    ->sum('t.total');
            case 'MAGANG':
                return DB::table('pendapatan_lain as t')
                    ->join('revenue_masters as rm', 't.revenue_master_id', '=', 'rm.id')
                    ->where('rm.tahun', $tahun)
                    ->where('rm.is_posted', 1)
                    ->whereBetween('t.tanggal', [$startDate, $endDate])
                    ->where('t.transaksi', 'like', '%Magang%')
                    ->sum('t.total');
            case 'PENELITIAN':
                return DB::table('pendapatan_lain as t')
                    ->join('revenue_masters as rm', 't.revenue_master_id', '=', 'rm.id')
                    ->where('rm.tahun', $tahun)
                    ->where('rm.is_posted', 1)
                    ->whereBetween('t.tanggal', [$startDate, $endDate])
                    ->where('t.transaksi', 'like', '%Penelitian%')
                    ->sum('t.total');
            case 'PERMINTAAN_DATA':
                return DB::table('pendapatan_lain as t')
                    ->join('revenue_masters as rm', 't.revenue_master_id', '=', 'rm.id')
                    ->where('rm.tahun', $tahun)
                    ->where('rm.is_posted', 1)
                    ->whereBetween('t.tanggal', [$startDate, $endDate])
                    ->where('t.transaksi', 'like', '%Permintaan Data%')
                    ->sum('t.total');
            case 'STUDY_BANDING':
                return DB::table('pendapatan_lain as t')
                    ->join('revenue_masters as rm', 't.revenue_master_id', '=', 'rm.id')
                    ->where('rm.tahun', $tahun)
                    ->where('rm.is_posted', 1)
                    ->whereBetween('t.tanggal', [$startDate, $endDate])
                    ->where('t.transaksi', 'like', '%Study Banding%')
                    ->sum('t.total');
            case 'LAIN_LAIN':
                return DB::table('pendapatan_lain as t')
                    ->join('revenue_masters as rm', 't.revenue_master_id', '=', 'rm.id')
                    ->where('rm.tahun', $tahun)
                    ->where('rm.is_posted', 1)
                    ->whereBetween('t.tanggal', [$startDate, $endDate])
                    ->where('t.transaksi', 'NOT LIKE', '%PKL%')
                    ->where('t.transaksi', 'NOT LIKE', '%Praktek Kerja Lapangan%')
                    ->where('t.transaksi', 'NOT LIKE', '%Magang%')
                    ->where('t.transaksi', 'NOT LIKE', '%Penelitian%')
                    ->where('t.transaksi', 'NOT LIKE', '%Permintaan Data%')
                    ->where('t.transaksi', 'NOT LIKE', '%Study Banding%')
                    ->sum('t.total');
        }
        return 0;
    }

    private function aggregateExpenditure($start, $end)
    {
        $data = DB::table('expenditures')
            ->join('kode_rekening', 'expenditures.kode_rekening_id', '=', 'kode_rekening.id')
            ->whereBetween('expenditures.spending_date', [$start, $end])
            ->select('kode_rekening.kode as kode_rekening', 'kode_rekening.nama as uraian', DB::raw('SUM(expenditures.gross_value) as jumlah'))
            ->groupBy('kode_rekening.kode', 'kode_rekening.nama')
            ->get();

        return $data->map(fn($item) => (array) $item)->toArray();
    }

    public function sahkan($id)
    {
        abort_unless(auth()->user()->hasPermission('SP3BP_APPROVE'), 403);
        $sp3bp = SP3BP::with('rekonsiliasi')->where('periode_id', $id)->firstOrFail();

        if ($sp3bp->selisih != 0) {
            return response()->json(['error' => 'Tidak bisa disahkan because ada selisih saldo!'], 422);
        }

        $sp3bp->status = 'final';
        $sp3bp->save();

        $periode = PengesahanPeriode::findOrFail($id);
        $periode->status = 'disahkan';
        $periode->tgl_pengesahan = now();
        $periode->save();

        return response()->json($sp3bp);
    }

    public function batalSah($id)
    {
        abort_unless(auth()->user()->hasPermission('SP3BP_APPROVE'), 403);
        $sp3bp = SP3BP::where('periode_id', $id)->firstOrFail();

        $sp3bp->status = 'draft';
        $sp3bp->save();

        $periode = PengesahanPeriode::findOrFail($id);
        $periode->status = 'draft';
        $periode->save();

        return response()->json($sp3bp);
    }
    public function printPdf($id)
    {
        abort_unless(auth()->user()->hasPermission('SP3BP_PRINT'), 403);
        $sp3bp = SP3BP::with(['periode', 'detailPendapatan', 'detailBelanja', 'rekonsiliasi'])
            ->where('periode_id', $id)
            ->firstOrFail();

        $pdf = Pdf::loadView('dashboard.exports.sp3bp_pdf', compact('sp3bp'))
            ->setPaper('f4', 'portrait');

        return $pdf->stream("SP3BP_{$sp3bp->periode->bulan}_{$sp3bp->periode->tahun}.pdf");
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->hasPermission('SP3BP_GENERATE'), 403);
        $periode = PengesahanPeriode::findOrFail($id);

        if ($periode->status === 'terkunci') {
            return response()->json(['error' => 'Periode sudah terkunci dan tidak dapat dihapus!'], 422);
        }

        $periode->delete();

        return response()->json(['message' => 'Periode berhasil dihapus']);
    }
}





