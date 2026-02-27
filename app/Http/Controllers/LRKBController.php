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
        $lrkbs = LRKB::orderBy('id', 'asc')->get();
        return response()->json($lrkbs);
    }

    public function store(Request $request)
    {
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
        $lrkb = LRKB::with(['details'])->findOrFail($id);
        return response()->json($lrkb);
    }

    public function generate($id)
    {
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

        $belanja = DB::table('expenditures')->whereBetween('spending_date', [$startDate, $endDate])->sum('gross_value');

        // 2. Get Saldo Awal (from previous LRKB)
        $saldoAwal = 0;
        if ($t) {
            $prevT = $t == 1 ? 4 : $t - 1;
            $prevYear = $t == 1 ? $year - 1 : $year;
            $prevLrkb = LRKB::where('tahun', $prevYear)->where('triwulan', $prevT)->where('status', 'valid')->first();
        } else {
            $prevM = $m == 1 ? 12 : $m - 1;
            $prevYear = $m == 1 ? $year - 1 : $year;
            $prevLrkb = LRKB::where('tahun', $prevYear)->where('bulan', $prevM)->where('status', 'valid')->first();
        }

        if ($prevLrkb) {
            $saldoAwal = $prevLrkb->saldo_fisik;
        }

        // 3. Get Physical Balances at the end of period
        $bku = $this->reportService->getBkuData($year, $endMonth);
        $summary = $bku['summary'] ?? [];
        $saldoBank = $summary['final_bank'] ?? 0;
        $saldoTunai = $summary['final_tunai'] ?? 0;
        $saldoFisik = $saldoBank + $saldoTunai;

        $saldoAkhirBuku = $saldoAwal + $pendapatan - $belanja;
        $selisih = round($saldoFisik - $saldoAkhirBuku, 2);

        // EXTRA: Calculate Physical In/Out for separation in view
        $bankIn = DB::table('bank_account_ledgers')->whereBetween('date', [$startDate, $endDate])->sum('debit');
        $bankOut = DB::table('bank_account_ledgers')->whereBetween('date', [$startDate, $endDate])->sum('credit');
        $tunaiIn = DB::table('treasurer_cash')->whereBetween('date', [$startDate, $endDate])->sum('debit');
        $tunaiOut = DB::table('treasurer_cash')->whereBetween('date', [$startDate, $endDate])->sum('credit');

        DB::beginTransaction();
        try {
            $lrkb->update([
                'saldo_awal' => $saldoAwal,
                'pendapatan' => $pendapatan,
                'belanja' => $belanja,
                'saldo_akhir_buku' => $saldoAkhirBuku,
                'saldo_fisik' => $saldoFisik,
                'saldo_bank' => $saldoBank,
                'saldo_tunai' => $saldoTunai,
                'selisih' => $selisih,
            ]);

            // Detailed snapshot (optional but good for audit)
            $lrkb->details()->delete();
            $lrkb->details()->createMany([
                ['jenis' => 'bank_penerimaan', 'uraian' => 'Arus Bank (Penerimaan)', 'jumlah' => $bankIn],
                ['jenis' => 'bank_pengeluaran', 'uraian' => 'Arus Bank (Pengeluaran)', 'jumlah' => $bankOut],
                ['jenis' => 'tunai_penerimaan', 'uraian' => 'Arus Tunai (Penerimaan)', 'jumlah' => $tunaiIn],
                ['jenis' => 'tunai_pengeluaran', 'uraian' => 'Arus Tunai (Pengeluaran)', 'jumlah' => $tunaiOut],
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
        $lrkb = LRKB::findOrFail($id);
        if ($lrkb->selisih != 0) {
            return response()->json(['error' => 'Rekonsiliasi tidak bisa divalidasi karena terdapat selisih kas!'], 422);
        }

        $lrkb->update(['status' => 'valid', 'tgl_rekonsiliasi' => now()]);
        return response()->json($lrkb);
    }

    public function unvalidateLrkb($id)
    {
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
        $lrkb = LRKB::findOrFail($id);
        if ($lrkb->status !== 'draft') {
            return response()->json(['error' => 'Hanya data draft yang dapat dihapus'], 422);
        }
        $lrkb->delete();
        return response()->json(['message' => 'Data berhasil dihapus']);
    }

    public function print($id)
    {
        $lrkb = LRKB::with(['details'])->findOrFail($id);
        $pdf = Pdf::loadView('dashboard.exports.lrkb_pdf', compact('lrkb'))
            ->setPaper('f4', 'portrait');
        return $pdf->stream("LRKB_{$lrkb->triwulan}_{$lrkb->tahun}.pdf");
    }
}
