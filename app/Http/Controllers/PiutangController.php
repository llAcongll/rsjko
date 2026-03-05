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
        abort_unless(auth()->user()->hasPermission('PIUTANG_VIEW') || auth()->user()->isAdmin(), 403);

        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');
        $status = $request->get('status'); // LUNAS | BELUM_LUNAS
        $sortBy = $request->get('sort_by', 'tanggal');
        $sortDir = $request->get('sort_dir', 'desc');

        $allowedSortColumns = ['tanggal', 'bulan_pelayanan', 'jumlah_piutang', 'status', 'id'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'tanggal';
        }

        $tahunAnggaran = session('tahun_anggaran') ?? now()->year;
        $query = Piutang::with('perusahaan')
            ->where('tahun', $tahunAnggaran);

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

        $query->orderBy($sortBy, $sortDir)->orderBy('id', $sortDir);

        if ($request->header('Accept') === 'application/json') {
            $totalQuery = clone $query;
            $paginated = $query->paginate($perPage);

            // Hitung total piutang berjalan dari tabel piutang
            $totalsPiutang = $totalQuery->reorder()->selectRaw('SUM(jumlah_piutang) as total_piutang')->first();

            // Ambil data potongan & adm bank dari tabel PenyesuaianPendapatan agar sinkron
            $penyesuaianQuery = \App\Models\PenyesuaianPendapatan::where('tahun', session('tahun_anggaran'));

            // Samakan filter pencarian jika ada
            if ($search) {
                $penyesuaianQuery->where(function ($q) use ($search) {
                    $q->where('keterangan', 'like', "%{$search}%")
                        ->orWhereHas('perusahaan', function ($p) use ($search) {
                            $p->where('nama', 'like', "%{$search}%");
                        });
                });
            }

            $totalsPenyesuaian = $penyesuaianQuery->selectRaw('
                SUM(potongan) as total_potongan,
                SUM(administrasi_bank) as total_adm_bank
            ')->first();

            return response()->json([
                'data' => $paginated->items(),
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem(),
                'total' => $paginated->total(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'aggregates' => [
                    'total_piutang' => $totalsPiutang->total_piutang ?? 0,
                    'total_potongan' => $totalsPenyesuaian->total_potongan ?? 0,
                    'total_adm_bank' => $totalsPenyesuaian->total_adm_bank ?? 0,
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
        abort_unless(auth()->user()->hasPermission('PIUTANG_CRUD') || auth()->user()->isAdmin(), 403);

        $data = $request->validate([
            'tanggal' => 'required|date',
            'perusahaan_id' => 'required|exists:perusahaans,id',
            'bulan_pelayanan' => 'required|string|max:50',
            'jumlah_piutang' => 'required|numeric|min:0',
            'potongan' => 'nullable|numeric|min:0',
            'administrasi_bank' => 'nullable|numeric|min:0',
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
        abort_unless(auth()->user()->hasPermission('PIUTANG_CRUD') || auth()->user()->isAdmin(), 403);

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
        abort_unless(auth()->user()->hasPermission('PIUTANG_VIEW'), 403);
        return Piutang::with('perusahaan')->findOrFail($id);
    }

    /* =========================
       DELETE
    ========================= */
    public function destroy($id)
    {
        abort_unless(auth()->user()->hasPermission('PIUTANG_CRUD') || auth()->user()->isAdmin(), 403);
        Piutang::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

}
