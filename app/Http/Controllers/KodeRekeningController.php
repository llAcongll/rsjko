<?php

namespace App\Http\Controllers;

use App\Models\KodeRekening;
use App\Models\ActivityLog;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class KodeRekeningController extends Controller
{
    protected $service;

    public function __construct(AccountService $service)
    {
        $this->service = $service;
    }

    /* =========================
       INDEX
       - View (HTML)
       - Tree JSON (AJAX)
    ========================= */
    public function index(Request $request)
    {
        $category = $request->get('category', 'PENDAPATAN');
        $permission = ($category === 'PENGELUARAN') ? 'KODE_REKENING_PENGELUARAN_VIEW' : 'KODE_REKENING_PENDAPATAN_VIEW';
        abort_unless(auth()->user()->hasPermission($permission) || auth()->user()->hasPermission('KODE_REKENING_VIEW'), 403);

        if ($request->expectsJson()) {
            return $this->service->buildTree($category);
        }

        if ($category === 'PENGELUARAN') {
            return view('dashboard.master.kode-rekening.expenditure');
        }

        return view('dashboard.master.kode-rekening.index');
    }

    /* =========================
       STORE
    ========================= */
    public function store(Request $request)
    {
        $category = $request->get('category');
        $permission = ($category === 'PENGELUARAN') ? 'KODE_REKENING_PENGELUARAN_CRUD' : 'KODE_REKENING_PENDAPATAN_CRUD';
        abort_unless(auth()->user()->hasPermission($permission) || auth()->user()->hasPermission('KODE_REKENING_CRUD'), 403);

        $data = $request->validate([
            'kode' => ['required', 'string', 'max:50'],
            'nama' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:kode_rekening,id'],
            'level' => ['required', 'integer', 'min:1'],
            'tipe' => ['required', Rule::in(['header', 'detail'])],
            'category' => ['required', Rule::in(['PENDAPATAN', 'PENGELUARAN'])],
            'sumber_data' => ['nullable', 'string'],
        ]);

        // Validasi level anak
        if ($data['parent_id']) {
            $parent = KodeRekening::findOrFail($data['parent_id']);

            if ((int) $data['level'] !== ((int) $parent->level + 1)) {
                return response()->json('Level tidak valid (harus parent level + 1)', 422);
            }
        }

        $rekening = KodeRekening::create($data);

        ActivityLog::log(
            'CREATE',
            'KODE_REKENING',
            "Menambah kode rekening: {$rekening->kode} - {$rekening->nama}",
            $rekening->id,
            null,
            $rekening->toArray()
        );

        return response()->json($rekening, 201);
    }

    /* =========================
       UPDATE
    ========================= */
    public function update(Request $request, $id)
    {
        $rekening = KodeRekening::findOrFail($id);
        $permission = ($rekening->category === 'PENGELUARAN') ? 'KODE_REKENING_PENGELUARAN_CRUD' : 'KODE_REKENING_PENDAPATAN_CRUD';
        abort_unless(auth()->user()->hasPermission($permission) || auth()->user()->hasPermission('KODE_REKENING_CRUD'), 403);

        $data = $request->validate([
            'kode' => ['required', 'string', 'max:50'],
            'nama' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:kode_rekening,id'],
            'level' => ['required', 'integer', 'min:1'],
            'tipe' => ['required', Rule::in(['header', 'detail'])],
            'category' => ['required', Rule::in(['PENDAPATAN', 'PENGELUARAN'])],
            'sumber_data' => ['nullable', 'string'],
        ]);

        // Tidak boleh ubah ke DETAIL jika masih punya anak
        if ($rekening->children()->exists() && $data['tipe'] === 'detail') {
            return response()->json('Tidak bisa mengubah ke DETAIL karena masih punya sub rekening', 422);
        }

        $oldValues = $rekening->toArray();
        $rekening->update($data);

        ActivityLog::log(
            'UPDATE',
            'KODE_REKENING',
            "Mengubah kode rekening: {$rekening->kode} - {$rekening->nama}",
            $rekening->id,
            $oldValues,
            $rekening->toArray()
        );

        return response()->json($rekening);
    }

    /* =========================
       DELETE
    ========================= */
    public function destroy($id)
    {
        $rekening = KodeRekening::findOrFail($id);
        $permission = ($rekening->category === 'PENGELUARAN') ? 'KODE_REKENING_PENGELUARAN_CRUD' : 'KODE_REKENING_PENDAPATAN_CRUD';
        abort_unless(auth()->user()->hasPermission($permission) || auth()->user()->hasPermission('KODE_REKENING_CRUD'), 403);

        try {
            // 1. Cek apakah masih memiliki sub-rekening (anak)
            if ($rekening->children()->exists()) {
                return response()->json([
                    'message' => 'Tidak bisa dihapus karena masih memiliki sub-rekening. Hapus semua sub-rekening di dalamnya terlebih dahulu.'
                ], 422);
            }

            // 2. Cek apakah sudah ada data Transaksi rill
            $tables = ['pendapatan_umum', 'pendapatan_bpjs', 'pendapatan_jaminan', 'pendapatan_lain', 'pendapatan_kerjasama', 'pengeluaran'];
            foreach ($tables as $table) {
                if (Schema::hasColumn($table, 'kode_rekening_id')) {
                    if (DB::table($table)->where('kode_rekening_id', $id)->exists()) {
                        return response()->json([
                            'message' => "Tidak bisa dihapus karena sudah memiliki data transaksi rill. Silakan hapus data terkait jika ingin menghapus kode ini."
                        ], 422);
                    }
                }
            }

            // 3. Penanganan Data Anggaran
            $oldValues = $rekening->toArray();
            $uraian = "{$rekening->kode} - {$rekening->nama}";

            DB::beginTransaction();
            DB::table('anggaran_rekening')->where('kode_rekening_id', $id)->delete();
            $rekening->delete();
            DB::commit();

            ActivityLog::log(
                'DELETE',
                'KODE_REKENING',
                "Menghapus kode rekening: {$uraian}",
                $id,
                $oldValues,
                null
            );

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus: ' . $e->getMessage()
            ], 500);
        }
    }

    /* =========================
       TREE + ANGARAN (READ ONLY)
    ========================= */
    public function treeAnggaran($tahun, Request $request)
    {
        $category = $request->get('category', 'PENDAPATAN');
        $permission = ($category === 'PENGELUARAN') ? 'KODE_REKENING_PENGELUARAN_VIEW' : 'KODE_REKENING_PENDAPATAN_VIEW';
        abort_unless(auth()->user()->hasPermission($permission) || auth()->user()->hasPermission('KODE_REKENING_VIEW'), 403);

        return $this->service->buildTree($category, $tahun);
    }
}
