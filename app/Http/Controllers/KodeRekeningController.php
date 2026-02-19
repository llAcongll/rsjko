<?php

namespace App\Http\Controllers;

use App\Models\KodeRekening;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class KodeRekeningController extends Controller
{
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
            return $this->buildTree($category);
        }

        if ($category === 'PENGELUARAN') {
            return view('dashboard.master.kode-rekening.expenditure');
        }

        return view('dashboard.master.kode-rekening.index');
    }

    /* =========================
       BUILD TREE (FLAT → TREE)
    ========================= */
    private function buildTree($category = 'PENDAPATAN')
    {
        $all = KodeRekening::where('category', $category)->orderBy('kode')->get();

        $items = [];
        foreach ($all as $row) {
            $items[$row->id] = [
                'id' => $row->id,
                'kode' => $row->kode,
                'nama' => $row->nama,
                'parent_id' => $row->parent_id,
                'level' => $row->level,
                'tipe' => $row->tipe,
                'sumber_data' => $row->sumber_data,
                'children' => []
            ];
        }

        $tree = [];
        foreach ($items as $id => &$item) {
            if ($item['parent_id']) {
                if (isset($items[$item['parent_id']])) {
                    $items[$item['parent_id']]['children'][] = &$item;
                }
            } else {
                $tree[] = &$item;
            }
        }

        return $tree;
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
                return response()->json(
                    'Level tidak valid (harus parent level + 1)',
                    422
                );
            }
        }

        $rekening = KodeRekening::create($data);

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
            'kode' => [
                'required',
                'string',
                'max:50',
            ],
            'nama' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:kode_rekening,id'],
            'level' => ['required', 'integer', 'min:1'],
            'tipe' => ['required', Rule::in(['header', 'detail'])],
            'category' => ['required', Rule::in(['PENDAPATAN', 'PENGELUARAN'])],
            'sumber_data' => ['nullable', 'string'],
        ]);

        // ❌ Tidak boleh ubah ke DETAIL jika masih punya anak
        if (
            $rekening->children()->exists() &&
            $data['tipe'] === 'detail'
        ) {
            return response()->json(
                'Tidak bisa mengubah ke DETAIL karena masih punya sub rekening',
                422
            );
        }

        $rekening->update($data);

        return response()->json($rekening);
    }

    public function destroy($id)
    {
        $rekening = KodeRekening::findOrFail($id);
        $permission = ($rekening->category === 'PENGELUARAN') ? 'KODE_REKENING_PENGELUARAN_CRUD' : 'KODE_REKENING_PENDAPATAN_CRUD';
        abort_unless(auth()->user()->hasPermission($permission) || auth()->user()->hasPermission('KODE_REKENING_CRUD'), 403);
        try {
            $rekening = KodeRekening::findOrFail($id);

            // 1. Cek apakah masih memiliki sub-rekening (anak)
            if ($rekening->children()->exists()) {
                return response()->json([
                    'message' => 'Tidak bisa dihapus karena masih memiliki sub-rekening. Hapus semua sub-rekening di dalamnya terlebih dahulu.'
                ], 422);
            }

            // 2. Cek apakah sudah ada data Transaksi Pendapatan (Realisasi)
            // Ini adalah data riil yang tidak boleh hilang referensinya.
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
            // Jika hanya data anggaran (rencana), kita izinkan hapus dengan membersihkan record anggarannya juga.
            $anggaran = DB::table('anggaran_rekening')->where('kode_rekening_id', $id)->first();
            if ($anggaran) {
                // Cek apakah ada rincian anggaran yang bersifat manual/detail
                $hasRincian = DB::table('anggaran_rincian')->where('anggaran_rekening_id', $anggaran->id)->exists();

                // Jika user sudah mengosongkan nilai (0) dan tidak ada rincian, atau memang ingin hapus akun:
                // Kita hapus dulu record anggarannya agar tidak melanggar foreign key saat menghapus kode_rekening
                DB::table('anggaran_rekening')->where('kode_rekening_id', $id)->delete();
            }

            $rekening->delete();
            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus: ' . $e->getMessage()
            ], 500);
        }
    }

    // =========================
    // TREE + ANGARAN (READ ONLY)
    // =========================
    public function treeAnggaran($tahun, Request $request)
    {
        $category = $request->get('category', 'PENDAPATAN');
        $permission = ($category === 'PENGELUARAN') ? 'KODE_REKENING_PENGELUARAN_VIEW' : 'KODE_REKENING_PENDAPATAN_VIEW';
        abort_unless(auth()->user()->hasPermission($permission) || auth()->user()->hasPermission('KODE_REKENING_VIEW'), 403);

        $roots = KodeRekening::with('children.children')
            ->whereNull('parent_id')
            ->where('category', $category)
            ->orderBy('kode')
            ->get();

        return $roots->map(function ($r) use ($tahun) {
            return $this->mapTree($r, $tahun);
        });
    }

    private function mapTree($rekening, $tahun)
    {
        return [
            'id' => $rekening->id,
            'kode' => $rekening->kode,
            'nama' => $rekening->nama,
            'tipe' => $rekening->tipe,
            'sumber_data' => $rekening->sumber_data,
            'total_anggaran' => $this->hitungTotalAnggaran($rekening, $tahun),
            'total_realisasi' => $this->hitungRealisasi($rekening, $tahun),
            'children' => $rekening->children->map(
                fn($c) =>
                $this->mapTree($c, $tahun)
            )
        ];
    }

    private function hitungTotalAnggaran($rekening, $tahun)
    {
        // kalau DETAIL → ambil langsung dari DB
        if ($rekening->tipe === 'detail') {
            return $rekening->anggaran()
                ->where('tahun', $tahun)
                ->value('nilai') ?? 0;
        }

        // kalau HEADER → jumlahkan anaknya
        $total = 0;
        foreach ($rekening->children as $child) {
            $total += $this->hitungTotalAnggaran($child, $tahun);
        }

        return $total;
    }

    private function hitungRealisasi($rekening, $tahun)
    {
        // DETAIL -> hitung realisasi berdasarkan mapping
        if ($rekening->tipe === 'detail') {
            $totalAmount = 0;

            if ($rekening->category === 'PENDAPATAN' && $rekening->sumber_data) {
                $totalAmount = 0;
                switch ($rekening->sumber_data) {
                    case 'PASIEN_UMUM':
                        $totalAmount = DB::table('pendapatan_umum')->where('tahun', $tahun)->sum('total');
                        break;
                    case 'BPJS_JAMINAN':
                        $bpjs = DB::table('pendapatan_bpjs')->where('tahun', $tahun)->sum('total');
                        $jam = DB::table('pendapatan_jaminan')->where('tahun', $tahun)->sum('total');
                        $totalAmount = $bpjs + $jam;
                        break;
                    case 'KERJASAMA':
                        $totalAmount = DB::table('pendapatan_kerjasama')->where('tahun', $tahun)->sum('total');
                        break;
                    case 'PKL':
                        $totalAmount = DB::table('pendapatan_lain')->where('tahun', $tahun)
                            ->where(fn($q) => $q->where('transaksi', 'like', '%PKL%')->orWhere('transaksi', 'like', '%Praktek Kerja Lapangan%'))->sum('total');
                        break;
                    case 'MAGANG':
                        $totalAmount = DB::table('pendapatan_lain')->where('tahun', $tahun)->where('transaksi', 'like', '%Magang%')->sum('total');
                        break;
                    case 'PENELITIAN':
                        $totalAmount = DB::table('pendapatan_lain')->where('tahun', $tahun)->where('transaksi', 'like', '%Penelitian%')->sum('total');
                        break;
                    case 'PERMINTAAN_DATA':
                        $totalAmount = DB::table('pendapatan_lain')->where('tahun', $tahun)->where('transaksi', 'like', '%Permintaan Data%')->sum('total');
                        break;
                    case 'STUDY_BANDING':
                        $totalAmount = DB::table('pendapatan_lain')->where('tahun', $tahun)->where('transaksi', 'like', '%Study Banding%')->sum('total');
                        break;
                    case 'LAIN_LAIN':
                        $totalAmount = DB::table('pendapatan_lain')->where('tahun', $tahun)
                            ->where('transaksi', 'NOT LIKE', '%PKL%')
                            ->where('transaksi', 'NOT LIKE', '%Praktek Kerja Lapangan%')
                            ->where('transaksi', 'NOT LIKE', '%Magang%')
                            ->where('transaksi', 'NOT LIKE', '%Penelitian%')
                            ->where('transaksi', 'NOT LIKE', '%Permintaan Data%')
                            ->where('transaksi', 'NOT LIKE', '%Study Banding%')
                            ->sum('total');
                        break;
                }
                return (int) $totalAmount;
            }

            if ($rekening->category === 'PENGELUARAN') {
                if ($rekening->sumber_data) {
                    return (int) DB::table('pengeluaran')
                        ->where('kategori', $rekening->sumber_data)
                        ->whereYear('tanggal', $tahun)
                        ->sum('nominal');
                }

                return (int) DB::table('pengeluaran')
                    ->where('kode_rekening_id', $rekening->id)
                    ->whereYear('tanggal', $tahun)
                    ->sum('nominal');
            }
        }

        // HEADER -> jumlah anak
        $total = 0;
        foreach ($rekening->children as $child) {
            $total += $this->hitungRealisasi($child, $tahun);
        }

        return $total;
    }

}
