<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PenyesuaianPendapatan;
use App\Models\Perusahaan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PenyesuaianPendapatanController extends Controller
{
    /* =========================
       LIST DATA (AJAX TABLE)
    ========================= */
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENYESUAIAN_VIEW'), 403);
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');
        $kategori = $request->get('kategori');

        $query = PenyesuaianPendapatan::with('perusahaan')
            ->where('tahun', session('tahun_anggaran'))
            ->orderBy('tanggal', 'asc');

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        if ($request->filled('sub_kategori')) {
            $query->where('sub_kategori', $request->sub_kategori);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('keterangan', 'like', "%{$search}%")
                    ->orWhereHas('perusahaan', function ($p) use ($search) {
                        $p->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->header('Accept') === 'application/json') {
            return response()->json($query->paginate($perPage));
        }

        return $query->paginate($perPage);
    }

    /* =========================
       STORE
    ========================= */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENYESUAIAN_CRUD'), 403);
        $data = $request->validate([
            'tanggal' => 'required|date',
            'kategori' => 'required|in:BPJS,JAMINAN',
            'sub_kategori' => 'nullable|string',
            'perusahaan_id' => 'required|exists:perusahaans,id',
            'potongan' => 'nullable|numeric|min:0',
            'administrasi_bank' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $data['tahun'] = session('tahun_anggaran') ?? Carbon::parse($data['tanggal'])->year;

        $penyesuaian = PenyesuaianPendapatan::create($data);

        return response()->json([
            'message' => 'Data penyesuaian berhasil disimpan',
            'data' => $penyesuaian
        ]);
    }

    /* =========================
       UPDATE
    ========================= */
    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->hasPermission('PENYESUAIAN_CRUD'), 403);
        $item = PenyesuaianPendapatan::findOrFail($id);
        $data = $request->validate([
            'tanggal' => 'required|date',
            'kategori' => 'required|in:BPJS,JAMINAN',
            'sub_kategori' => 'nullable|string',
            'perusahaan_id' => 'required|exists:perusahaans,id',
            'potongan' => 'nullable|numeric|min:0',
            'administrasi_bank' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $data['tahun'] = session('tahun_anggaran') ?? Carbon::parse($data['tanggal'])->year;
        $item->update($data);

        return response()->json([
            'message' => 'Data penyesuaian berhasil diperbarui',
            'data' => $item
        ]);
    }

    /* =========================
       DELETE
    ========================= */
    public function destroy($id)
    {
        abort_unless(auth()->user()->hasPermission('PENYESUAIAN_CRUD'), 403);
        $item = PenyesuaianPendapatan::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => 'Data penyesuaian berhasil dihapus'
        ]);
    }
}
