<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengeluaran;
use App\Models\KodeRekening;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PengeluaranController extends Controller
{
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
                    ->orWhereHas('kodeRekening', function ($qr) use ($search) {
                        $qr->where('nama', 'like', "%$search%")
                            ->orWhere('kode', 'like', "%$search%");
                    });
            });
        }



        $totalNominal = (float) $query->sum('nominal');
        $totalCount = $query->count();
        $data = $query->orderBy('tanggal', 'desc')->paginate($limit);

        $response = $data->toArray();
        $response['aggregates'] = [
            'total_nominal' => $totalNominal,
            'total_count' => $totalCount
        ];

        return response()->json($response);
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
            'keterangan' => 'nullable|string',
        ]);

        $pengeluaran = Pengeluaran::create($data);

        ActivityLog::log(
            'CREATE',
            'PENGELUARAN',
            "Menambah pengeluaran: {$pengeluaran->uraian}",
            $pengeluaran->id,
            null,
            $pengeluaran->toArray()
        );

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
        $pengeluaran = Pengeluaran::findOrFail($id);

        $data = $request->validate([
            'tanggal' => 'required|date',
            'kategori' => 'required|in:PEGAWAI,BARANG_JASA,MODAL',
            'kode_rekening_id' => 'required|exists:kode_rekening,id',
            'uraian' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $oldValues = $pengeluaran->toArray();
        $pengeluaran->update($data);

        ActivityLog::log(
            'UPDATE',
            'PENGELUARAN',
            "Mengubah pengeluaran: {$pengeluaran->uraian}",
            $pengeluaran->id,
            $oldValues,
            $pengeluaran->toArray()
        );

        return response()->json($pengeluaran);
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_DELETE'), 403);
        $pengeluaran = Pengeluaran::findOrFail($id);
        $oldValues = $pengeluaran->toArray();
        $pengeluaran->delete();

        ActivityLog::log(
            'DELETE',
            'PENGELUARAN',
            "Menghapus pengeluaran: {$pengeluaran->uraian}",
            $id,
            $oldValues,
            null
        );

        return response()->json(['status' => 'ok']);
    }
}
