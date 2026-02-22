<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengeluaran;
use App\Services\PengeluaranService;
use Illuminate\Support\Facades\DB;

class PengeluaranController extends Controller
{
    protected $service;

    public function __construct(PengeluaranService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_VIEW'), 403);
        $kategori = $request->get('kategori');
        $search = $request->get('search');
        $limit = $request->get('limit', 10);

        $query = Pengeluaran::with('kodeRekening');

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('uraian', 'like', "%$search%")
                    ->orWhere('no_spp', 'like', "%$search%")
                    ->orWhere('no_spm', 'like', "%$search%")
                    ->orWhere('no_sp2d', 'like', "%$search%")
                    ->orWhereHas('kodeRekening', function ($qr) use ($search) {
                        $qr->where('nama', 'like', "%$search%")
                            ->orWhere('kode', 'like', "%$search%");
                    });
            });
        }

        // Clone query for aggregates
        $totalNominal = (float) (clone $query)->sum('nominal');
        $totalPajak = (float) (clone $query)->sum('potongan_pajak');
        $totalDibayarkan = (float) (clone $query)->sum('total_dibayarkan');
        $totalCount = (clone $query)->count();

        $aggMetode = (clone $query)
            ->whereIn('metode_pembayaran', ['UP', 'GU', 'LS'])
            ->select('metode_pembayaran', DB::raw('SUM(nominal) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('metode_pembayaran')
            ->get()
            ->keyBy('metode_pembayaran')
            ->toArray();

        $data = $query->orderBy('tanggal', 'asc')->paginate($limit);

        $response = $data->toArray();
        $response['aggregates'] = [
            'total_nominal' => $totalNominal,
            'total_pajak' => $totalPajak,
            'total_dibayarkan' => $totalDibayarkan,
            'total_count' => $totalCount,
            'total_up' => (float) ($aggMetode['UP']['total'] ?? 0),
            'count_up' => (int) ($aggMetode['UP']['count'] ?? 0),
            'total_gu' => (float) ($aggMetode['GU']['total'] ?? 0),
            'count_gu' => (int) ($aggMetode['GU']['count'] ?? 0),
            'total_ls' => (float) ($aggMetode['LS']['total'] ?? 0),
            'count_ls' => (int) ($aggMetode['LS']['count'] ?? 0),
        ];

        return response()->json($response);
    }

    public function generateNextSppNumber(Request $request)
    {
        $id = $request->get('id');
        $tanggal = $request->get('tanggal', date('Y-m-d'));
        $metode = $request->get('metode', 'UP');

        $preview = $this->service->previewNumbers($tanggal, $metode, $id);

        return response()->json([
            'no_spp' => $preview['no_spp'],
            'no_spm' => $preview['no_spm'],
            'no_sp2d' => $preview['no_sp2d'],
            'spp_index' => $preview['indexes']['spp_index'],
            'spp_metode_index' => $preview['indexes']['spp_metode_index'],
            'spm_index' => $preview['indexes']['spp_index'],
            'spm_metode_index' => $preview['indexes']['spp_metode_index'],
            'sp2d_index' => $preview['indexes']['spp_index']
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_CREATE'), 403);
        $data = $request->validate([
            'tanggal' => 'required|date',
            'kategori' => 'required|in:PEGAWAI,BARANG_JASA,MODAL',
            'kode_rekening_id' => 'required|exists:kode_rekening,id',
            'uraian' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'potongan_pajak' => 'nullable|numeric|min:0',
            'total_dibayarkan' => 'nullable|numeric|min:0',
            'metode_pembayaran' => 'nullable|in:UP,GU,LS',
            'no_spm' => 'nullable|string|max:100',
            'no_sp2d' => 'nullable|string|max:100',
            'no_spp' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string',
        ]);

        $budgetCheck = $this->service->checkBudget($data['kode_rekening_id'], $data['tanggal'], $data['nominal']);

        if (!$budgetCheck['isValid']) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'nominal' => ["Nominal melebihi sisa anggaran (Sisa Anggaran: Rp " . number_format($budgetCheck['sisa'], 0, ',', '.') . ")"]
                ]
            ], 422);
        }

        $pengeluaran = $this->service->store($data);

        return response()->json($pengeluaran, 201);
    }

    public function show($id)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_VIEW'), 403);
        $pengeluaran = Pengeluaran::with('kodeRekening')->findOrFail($id);
        return response()->json($pengeluaran);
    }

    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_UPDATE'), 403);
        $data = $request->validate([
            'tanggal' => 'required|date',
            'kategori' => 'required|in:PEGAWAI,BARANG_JASA,MODAL',
            'kode_rekening_id' => 'required|exists:kode_rekening,id',
            'uraian' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'potongan_pajak' => 'nullable|numeric|min:0',
            'total_dibayarkan' => 'nullable|numeric|min:0',
            'metode_pembayaran' => 'nullable|in:UP,GU,LS',
            'no_spm' => 'nullable|string|max:100',
            'no_sp2d' => 'nullable|string|max:100',
            'no_spp' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string',
        ]);

        $budgetCheck = $this->service->checkBudget($data['kode_rekening_id'], $data['tanggal'], $data['nominal'], $id);

        if (!$budgetCheck['isValid']) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'nominal' => ["Nominal melebihi sisa anggaran (Sisa Anggaran: Rp " . number_format($budgetCheck['sisa'], 0, ',', '.') . ")"]
                ]
            ], 422);
        }

        $pengeluaran = $this->service->update($id, $data);

        return response()->json($pengeluaran);
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_DELETE'), 403);
        $this->service->delete($id);

        return response()->json(['status' => 'ok']);
    }
}
