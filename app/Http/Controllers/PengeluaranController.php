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
                    ->orWhere('no_spp', 'like', "%$search%")
                    ->orWhere('no_spm', 'like', "%$search%")
                    ->orWhere('no_sp2d', 'like', "%$search%")
                    ->orWhereHas('kodeRekening', function ($qr) use ($search) {
                        $qr->where('nama', 'like', "%$search%")
                            ->orWhere('kode', 'like', "%$search%");
                    });
            });
        }



        $totalNominal = (float) $query->sum('nominal');
        $totalCount = $query->count();

        // Aggregates by payment method
        $aggMetode = Pengeluaran::query()
            ->when($kategori, fn($q) => $q->where('kategori', $kategori))
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
            'total_pajak' => (float) $query->sum('potongan_pajak'),
            'total_dibayarkan' => (float) $query->sum('total_dibayarkan'),
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
        $tanggal = $request->get('tanggal', date('Y-m-d'));
        $metode = $request->get('metode', 'UP');
        $year = date('Y', strtotime($tanggal));
        $month = (int) date('m', strtotime($tanggal));
        $monthRoman = $this->getRoman($month);

        // SPP Indexes
        $nextSppGlobal = (Pengeluaran::whereYear('tanggal', $year)->max('no_spp_index') ?? 0) + 1;
        $nextSppMetode = (Pengeluaran::whereYear('tanggal', $year)->where('metode_pembayaran', $metode)->max('no_spp_metode_index') ?? 0) + 1;

        // SPM Indexes
        $nextSpmGlobal = (Pengeluaran::whereYear('tanggal', $year)->max('no_spm_index') ?? 0) + 1;
        $nextSpmMetode = (Pengeluaran::whereYear('tanggal', $year)->where('metode_pembayaran', $metode)->max('no_spm_metode_index') ?? 0) + 1;

        // SP2D Index
        $nextSp2dGlobal = (Pengeluaran::whereYear('tanggal', $year)->max('no_sp2d_index') ?? 0) + 1;

        $gspp = str_pad($nextSppGlobal, 4, '0', STR_PAD_LEFT);
        $mspp = str_pad($nextSppMetode, 4, '0', STR_PAD_LEFT);
        $gspm = str_pad($nextSpmGlobal, 4, '0', STR_PAD_LEFT);
        $mspm = str_pad($nextSpmMetode, 4, '0', STR_PAD_LEFT);
        $gsp2d = str_pad($nextSp2dGlobal, 4, '0', STR_PAD_LEFT);

        return response()->json([
            'no_spp' => "{$gspp}/SPP/{$metode}-{$mspp}/BLUD/RSJKO-EHD/{$monthRoman}/{$year}",
            'no_spm' => "{$gspm}/SPM/{$metode}-{$mspm}/BLUD/RSJKO-EHD/{$monthRoman}/{$year}",
            'no_sp2d' => "{$gsp2d}/SP2D/1.02.01.03/{$year}",
            'spp_index' => $nextSppGlobal,
            'spp_metode_index' => $nextSppMetode,
            'spm_index' => $nextSpmGlobal,
            'spm_metode_index' => $nextSpmMetode,
            'sp2d_index' => $nextSp2dGlobal
        ]);
    }

    private function getRoman($number)
    {
        $map = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII'
        ];
        return $map[$number] ?? 'I';
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

        $year = date('Y', strtotime($data['tanggal']));
        $month = (int) date('m', strtotime($data['tanggal']));
        $monthRoman = $this->getRoman($month);
        $metode = $data['metode_pembayaran'];

        $data['potongan_pajak'] = $data['potongan_pajak'] ?? 0;
        $data['total_dibayarkan'] = max(0, $data['nominal'] - $data['potongan_pajak']);

        // Cek Anggaran vs Realisasi
        $anggaran = \App\Models\AnggaranRekening::where('tahun', $year)
            ->where('kode_rekening_id', $data['kode_rekening_id'])
            ->sum('nilai');

        $realisasiSaatIni = \App\Models\Pengeluaran::whereYear('tanggal', $year)
            ->where('kode_rekening_id', $data['kode_rekening_id'])
            ->sum('nominal');

        $sisaAnggaran = $anggaran - $realisasiSaatIni;

        if ($data['nominal'] > $sisaAnggaran) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'nominal' => ["Nominal melebihi sisa anggaran (Sisa Anggaran: Rp " . number_format($sisaAnggaran, 0, ',', '.') . ")"]
                ]
            ], 422);
        }

        // SPP Numbering
        if ($data['no_spp'] && $metode) {
            $data['no_spp_index'] = (Pengeluaran::whereYear('tanggal', $year)->max('no_spp_index') ?? 0) + 1;
            $data['no_spp_metode_index'] = (Pengeluaran::whereYear('tanggal', $year)->where('metode_pembayaran', $metode)->max('no_spp_metode_index') ?? 0) + 1;

            $gspp = str_pad($data['no_spp_index'], 4, '0', STR_PAD_LEFT);
            $mspp = str_pad($data['no_spp_metode_index'], 4, '0', STR_PAD_LEFT);
            $data['no_spp'] = "{$gspp}/SPP/{$metode}-{$mspp}/BLUD/RSJKO-EHD/{$monthRoman}/{$year}";
        }

        // SPM Numbering
        if ($data['no_spm'] && $metode) {
            $data['no_spm_index'] = (Pengeluaran::whereYear('tanggal', $year)->max('no_spm_index') ?? 0) + 1;
            $data['no_spm_metode_index'] = (Pengeluaran::whereYear('tanggal', $year)->where('metode_pembayaran', $metode)->max('no_spm_metode_index') ?? 0) + 1;

            $gspm = str_pad($data['no_spm_index'], 4, '0', STR_PAD_LEFT);
            $mspm = str_pad($data['no_spm_metode_index'], 4, '0', STR_PAD_LEFT);
            $data['no_spm'] = "{$gspm}/SPM/{$metode}-{$mspm}/BLUD/RSJKO-EHD/{$monthRoman}/{$year}";
        }

        // SP2D Numbering
        if ($data['no_sp2d']) {
            $data['no_sp2d_index'] = (Pengeluaran::whereYear('tanggal', $year)->max('no_sp2d_index') ?? 0) + 1;
            $gs2d = str_pad($data['no_sp2d_index'], 4, '0', STR_PAD_LEFT);
            $data['no_sp2d'] = "{$gs2d}/SP2D/1.02.01.03/{$year}";
        }

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
            'potongan_pajak' => 'nullable|numeric|min:0',
            'total_dibayarkan' => 'nullable|numeric|min:0',
            'metode_pembayaran' => 'nullable|in:UP,GU,LS',
            'no_spm' => 'nullable|string|max:100',
            'no_sp2d' => 'nullable|string|max:100',
            'no_spp' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string',
        ]);

        $year = date('Y', strtotime($data['tanggal']));

        $data['potongan_pajak'] = $data['potongan_pajak'] ?? 0;
        $data['total_dibayarkan'] = max(0, $data['nominal'] - $data['potongan_pajak']);

        // Cek Anggaran vs Realisasi
        $anggaran = \App\Models\AnggaranRekening::where('tahun', $year)
            ->where('kode_rekening_id', $data['kode_rekening_id'])
            ->sum('nilai');

        $realisasiSaatIni = \App\Models\Pengeluaran::whereYear('tanggal', $year)
            ->where('kode_rekening_id', $data['kode_rekening_id'])
            ->where('id', '!=', $id) // Exclude current transaction
            ->sum('nominal');

        $sisaAnggaran = $anggaran - $realisasiSaatIni;

        if ($data['nominal'] > $sisaAnggaran) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'nominal' => ["Nominal melebihi sisa anggaran (Sisa Anggaran: Rp " . number_format($sisaAnggaran, 0, ',', '.') . ")"]
                ]
            ], 422);
        }

        // If no_spp is being updated or date/method changed and it was an auto-gen
        // For simplicity, if it's an update, we usually don't change the number unless explicitly requested
        // But if the user didn't change it, keep it. If they changed method, maybe it should change?
        // Let's keep existing logic unless no_spp is empty
        if (!$data['no_spp'] && $data['metode_pembayaran']) {
            // Regeneration logic could go here if needed
        }

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
