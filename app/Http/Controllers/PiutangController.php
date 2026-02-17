<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Piutang;
use App\Models\Perusahaan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PiutangController extends Controller
{
    /* =========================
       LIST DATA (AJAX TABLE)
    ========================= */
    public function index(Request $request)
    {
        // TODO: Add permission check later if needed
        abort_unless(auth()->user()->hasPermission('PIUTANG_VIEW'), 403);

        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');
        $status = $request->get('status'); // LUNAS | BELUM_LUNAS

        $query = Piutang::with('perusahaan')
            ->where('tahun', session('tahun_anggaran'))
            ->orderBy('tanggal', 'asc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('bulan_pelayanan', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%")
                    ->orWhereHas('perusahaan', function ($p) use ($search) {
                        $p->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->header('Accept') === 'application/json') {
            $totalQuery = clone $query;
            $paginated = $query->paginate($perPage);

            // Hitung total keseluruhan berdasarkan filter (hanya Gross Piutang)
            $totals = $totalQuery->reorder()->selectRaw('SUM(jumlah_piutang) as total_piutang')->first();

            return response()->json([
                'data' => $paginated->items(),
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem(),
                'total' => $paginated->total(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'aggregates' => [
                    'total_piutang' => $totals->total_piutang ?? 0,
                ]
            ]);
        }

        // Return View (Main Page)
        return view('dashboard.pages.piutang');
    }

    /* =========================
       STORE
    ========================= */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PIUTANG_CRUD'), 403);

        $data = $request->validate([
            'tanggal' => 'required|date',
            'perusahaan_id' => 'required|exists:perusahaans,id',
            'bulan_pelayanan' => 'required|string|max:50',
            'jumlah_piutang' => 'required|numeric|min:0',
            'status' => 'required|in:LUNAS,BELUM_LUNAS',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $data['potongan'] = 0;
        $data['administrasi_bank'] = 0;
        $data['total_diterima'] = $data['jumlah_piutang'];
        $data['tahun'] = session('tahun_anggaran');

        Piutang::create($data);

        return response()->json(['success' => true]);
    }

    /* =========================
       UPDATE
    ========================= */
    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->hasPermission('PIUTANG_CRUD'), 403);

        $piutang = Piutang::findOrFail($id);

        $data = $request->validate([
            'tanggal' => 'required|date',
            'perusahaan_id' => 'required|exists:perusahaans,id',
            'bulan_pelayanan' => 'required|string|max:50',
            'jumlah_piutang' => 'required|numeric|min:0',
            'status' => 'required|in:LUNAS,BELUM_LUNAS',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $data['potongan'] = 0;
        $data['administrasi_bank'] = 0;
        $data['total_diterima'] = $data['jumlah_piutang'];

        $piutang->update($data);

        return response()->json(['success' => true]);
    }

    /* =========================
       SHOW
    ========================= */
    public function show($id)
    {
        return Piutang::with('perusahaan')->findOrFail($id);
    }

    /* =========================
       DELETE
    ========================= */
    public function destroy($id)
    {
        abort_unless(auth()->user()->hasPermission('PIUTANG_CRUD'), 403);
        Piutang::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

}
