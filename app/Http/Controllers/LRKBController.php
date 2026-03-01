<?php

namespace App\Http\Controllers;

use App\Models\LRKB;
use App\Models\LRKBDetail;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LRKBController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        abort_unless(auth()->user()->hasPermission('PENGESAHAN_VIEW'), 403);
        $lrkbs = LRKB::orderBy('id', 'asc')->get();
        return response()->json($lrkbs);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENGESAHAN_CREATE'), 403);
        $request->validate([
            'triwulan' => 'nullable|integer|between:1,4',
            'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'required|integer',
        ]);

        $query = LRKB::where('tahun', $request->tahun);
        if ($request->triwulan) {
            $query->where('triwulan', $request->triwulan);
        } else {
            $query->where('bulan', $request->bulan);
        }

        if ($query->exists()) {
            return response()->json(['error' => 'Rekonsiliasi periode ini sudah ada'], 422);
        }

        $lrkb = LRKB::create([
            'triwulan' => $request->triwulan,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        return response()->json($lrkb);
    }

    public function show($id)
    {
        abort_unless(auth()->user()->hasPermission('PENGESAHAN_VIEW'), 403);
        $lrkb = LRKB::with(['details'])->findOrFail($id);
        return response()->json($lrkb);
    }

    public function generate($id)
    {
        abort_unless(auth()->user()->hasPermission('PENGESAHAN_CREATE'), 403);
        $lrkb = LRKB::findOrFail($id);
        if ($lrkb->status === 'dikunci') {
            return response()->json(['error' => 'Data sudah dikunci'], 422);
        }

        $year = $lrkb->tahun;
        $t = $lrkb->triwulan;
        $m = $lrkb->bulan;

        if ($t) {
            $startMonth = ($t - 1) * 3 + 1;
            $endMonth = $t * 3;
        } else {
            $startMonth = $m;
            $endMonth = $m;
        }

        $startDate = Carbon::create($year, $startMonth, 1)->toDateString();
        $endDate = Carbon::create($year, $endMonth, 1)->endOfMonth()->toDateString();

        // 1. Calculate Mutasi (Income & Expense)
        $pendapatan = DB::table('pendapatan_umum')->whereBetween('tanggal', [$startDate, $endDate])->where('tahun', $year)->sum('total')
            + DB::table('pendapatan_bpjs')->whereBetween('tanggal', [$startDate, $endDate])->where('tahun', $year)->sum('total')
            + DB::table('pendapatan_jaminan')->whereBetween('tanggal', [$startDate, $endDate])->where('tahun', $year)->sum('total')
            + DB::table('pendapatan_kerjasama')->whereBetween('tanggal', [$startDate, $endDate])->where('tahun', $year)->sum('total')
            + DB::table('pendapatan_lain')->whereBetween('tanggal', [$startDate, $endDate])->where('tahun', $year)->sum('total');

        $penyesuaian = DB::table('penyesuaian_pendapatans')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->where('tahun', $year)
            ->sum(DB::raw('IFNULL(potongan, 0) + IFNULL(administrasi_bank, 0)'));

        $pendapatan -= $penyesuaian;

        $belanja = DB::table('expenditures')->whereBetween('spending_date', [$startDate, $endDate])->sum('gross_value');

        // 2. Get Saldo Awal (from MOST RECENT previous LRKB)
        $saldoAwal = 0;
        if ($t) {
            $prevT = $t == 1 ? 4 : $t - 1;
            $prevYear = $t == 1 ? $year - 1 : $year;
            $prevLrkb = LRKB::where('tahun', $prevYear)->where('triwulan', $prevT)->where('status', 'valid')->first();
        } else {
            // Check for previous month OR previous quarter that includes the previous month
            $prevM = $m == 1 ? 12 : $m - 1;
            $prevYear = $m == 1 ? $year - 1 : $year;

            $prevLrkb = LRKB::where('tahun', $prevYear)
                ->where(function ($q) use ($prevM) {
                    $q->where('bulan', $prevM)
                        ->orWhere('triwulan', ceil($prevM / 3));
                })
                ->where('status', 'valid')
                ->orderBy('triwulan', 'desc') // Prefer monthly if both exist? usually only one
                ->first();
        }

        if ($prevLrkb) {
            $saldoAwal = $prevLrkb->saldo_fisik;
        }

        // 3. Get Physical Balances at the end of period from BKU
        $bku = $this->reportService->getBkuData($year, $endMonth);
        $summary = $bku['summary'] ?? [];
        $saldoBank = $summary['final_bank'] ?? 0;
        $saldoTunai = $summary['final_tunai'] ?? 0;
        $saldoFisik = $saldoBank + $saldoTunai;

        // 4. Calculate Book Balance
        // Note: Pendapatan here is from Revenue Modules, which might differ from BKU if not synced
        $saldoAkhirBuku = $saldoAwal + $pendapatan - $belanja;
        $selisih = round($saldoFisik - $saldoAkhirBuku, 2);

        // 5. Calculate Physical Flows (Arus) for the period
        $bankIn = DB::table('bank_account_ledgers')->whereBetween('date', [$startDate, $endDate])->sum('debit') ?? 0;
        $bankOut = DB::table('bank_account_ledgers')->whereBetween('date', [$startDate, $endDate])->sum('credit') ?? 0;
        $tunaiIn = DB::table('treasurer_cash')->whereBetween('date', [$startDate, $endDate])->sum('debit') ?? 0;
        $tunaiOut = DB::table('treasurer_cash')->whereBetween('date', [$startDate, $endDate])->sum('credit') ?? 0;

        DB::beginTransaction();
        try {
            $lrkb->update([
                'saldo_awal' => $saldoAwal,
                'pendapatan' => $pendapatan,
                'belanja' => $belanja,
                'bank_masuk' => $bankIn,
                'bank_keluar' => $bankOut,
                'tunai_masuk' => $tunaiIn,
                'tunai_keluar' => $tunaiOut,
                'saldo_akhir_buku' => $saldoAkhirBuku,
                'saldo_fisik' => $saldoFisik,
                'saldo_bank' => $saldoBank,
                'saldo_tunai' => $saldoTunai,
                'selisih' => $selisih,
            ]);

            // Detailed snapshot rows for the table view
            $lrkb->details()->delete();
            $lrkb->details()->createMany([
                ['jenis' => 'bank_masuk', 'uraian' => 'Arus Bank (Masuk)', 'jumlah' => $bankIn],
                ['jenis' => 'bank_keluar', 'uraian' => 'Arus Bank (Keluar)', 'jumlah' => $bankOut],
                ['jenis' => 'tunai_masuk', 'uraian' => 'Arus Tunai (Masuk)', 'jumlah' => $tunaiIn],
                ['jenis' => 'tunai_keluar', 'uraian' => 'Arus Tunai (Keluar)', 'jumlah' => $tunaiOut],
            ]);

            DB::commit();
            return response()->json($lrkb->load('details'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function validateLrkb($id)
    {
        abort_unless(auth()->user()->hasPermission('PENGESAHAN_POST'), 403);
        $lrkb = LRKB::findOrFail($id);
        if ($lrkb->selisih != 0) {
            return response()->json(['error' => 'Rekonsiliasi tidak bisa divalidasi karena terdapat selisih kas!'], 422);
        }

        $lrkb->update(['status' => 'valid', 'tgl_rekonsiliasi' => now()]);
        return response()->json($lrkb);
    }

    public function unvalidateLrkb($id)
    {
        abort_unless(auth()->user()->hasPermission('PENGESAHAN_POST'), 403);
        $lrkb = LRKB::findOrFail($id);

        // Check if used by final SP3BP
        $sp3bpExist = \App\Models\SP3BP::whereHas('periode', function ($q) use ($lrkb) {
            $q->where('tahun', $lrkb->tahun);
            $q->where(function ($qq) use ($lrkb) {
                if ($lrkb->triwulan) {
                    $qq->where('triwulan', $lrkb->triwulan);
                } else {
                    $qq->where('bulan', $lrkb->bulan)
                        ->orWhere('triwulan', ceil($lrkb->bulan / 3));
                }
            });
        })->where('status', 'final')->exists();

        if ($sp3bpExist) {
            return response()->json(['error' => 'Gagal membuka validasi! Data ini sudah digunakan pada SP3BP yang sudah final.'], 422);
        }

        $lrkb->update(['status' => 'draft']);
        return response()->json($lrkb);
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->hasPermission('PENGESAHAN_DELETE'), 403);
        $lrkb = LRKB::findOrFail($id);
        if ($lrkb->status !== 'draft') {
            return response()->json(['error' => 'Hanya data draft yang dapat dihapus'], 422);
        }
        $lrkb->delete();
        return response()->json(['message' => 'Data berhasil dihapus']);
    }

    public function saveCatatan(Request $request, $id)
    {
        abort_unless(auth()->user()->hasPermission('PENGESAHAN_CREATE'), 403);
        $lrkb = LRKB::findOrFail($id);
        if ($lrkb->status !== 'draft') {
            return response()->json(['error' => 'Catatan hanya dapat diubah pada status Draft'], 422);
        }

        $lrkb->update(['catatan_selisih' => $request->catatan]);
        return response()->json(['success' => true]);
    }

    public function print($id)
    {
        abort_unless(auth()->user()->hasPermission('PENGESAHAN_VIEW'), 403);
        $lrkb = LRKB::with(['details'])->findOrFail($id);
        $pdf = Pdf::loadView('dashboard.exports.lrkb_pdf', compact('lrkb'))
            ->setPaper('f4', 'portrait');
        return $pdf->stream("LRKB_{$lrkb->triwulan}_{$lrkb->tahun}.pdf");
    }
}
