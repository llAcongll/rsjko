<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PendapatanBpjs;
use App\Models\Ruangan;
use App\Models\Perusahaan;
use App\Models\ActivityLog;
use App\Services\RevenueService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PendapatanBpjsController extends Controller
{
    protected $service;

    public function __construct(RevenueService $service)
    {
        $this->service = $service;
    }

    /* =========================
       LIST DATA (AJAX TABLE)
    ========================= */
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENDAPATAN_BPJS_VIEW'), 403);
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');
        $jenisBpjs = $request->get('jenis_bpjs');

        $query = PendapatanBpjs::with('ruangan', 'perusahaan')
            ->where('tahun', session('tahun_anggaran'))
            ->orderBy('tanggal', 'asc');

        if ($jenisBpjs) {
            $query->where('jenis_bpjs', $jenisBpjs);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $dateSearch = $this->service->parseDate($search) ?? $search;

                $q->where('nama_pasien', 'like', "%{$search}%")
                    ->orWhere('no_sep', 'like', "%{$search}%")
                    ->orWhereDate('tanggal', '=', $dateSearch)
                    ->orWhere('tanggal', 'like', "%{$search}%")
                    ->orWhereHas('ruangan', function ($r) use ($search) {
                        $r->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->header('Accept') === 'application/json') {
            $totalQuery = clone $query;
            $paginated = $query->paginate($perPage);

            $totals = $totalQuery->reorder()->selectRaw('
                SUM(rs_tindakan + rs_obat) as total_rs,
                SUM(pelayanan_tindakan + pelayanan_obat) as total_pelayanan,
                SUM(total) as grand_total
            ')->first();

            $rs = $totals->total_rs ?? 0;
            $pelayanan = $totals->total_pelayanan ?? 0;
            $total = $totals->grand_total ?? 0;

            $dedQuery = DB::table('penyesuaian_pendapatans')
                ->where('kategori', 'BPJS')
                ->where('tahun', session('tahun_anggaran'));

            if ($jenisBpjs) {
                $dedQuery->where('sub_kategori', $jenisBpjs);
            }

            $ded = $dedQuery->selectRaw('SUM(potongan) as total_potongan, SUM(administrasi_bank) as total_adm')
                ->first();

            $potongan = $ded->total_potongan ?? 0;
            $adm = $ded->total_adm ?? 0;

            if ($potongan > 0 || $adm > 0) {
                $grossQuery = DB::table('pendapatan_bpjs')->where('tahun', session('tahun_anggaran'));
                if ($jenisBpjs) {
                    $grossQuery->where('jenis_bpjs', $jenisBpjs);
                }
                $grossYearly = $grossQuery->sum('total');

                $ratio = $grossYearly > 0 ? ($total / $grossYearly) : 0;
                $dedPotongan = $potongan * $ratio;
                $dedAdm = $adm * $ratio;

                $rs -= round($dedPotongan * 0.7, 2);
                $pelayanan -= round($dedPotongan * 0.3, 2);
                $rs -= $dedAdm;
                $total = $rs + $pelayanan;
            }

            return response()->json([
                'data' => $paginated->items(),
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem(),
                'total' => $paginated->total(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'aggregates' => [
                    'total_rs' => max(0, $rs),
                    'total_pelayanan' => max(0, $pelayanan),
                    'total_all' => max(0, $total),
                ]
            ]);
        }

        return $query->paginate($perPage);
    }

    /* =========================
       STORE
    ========================= */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENDAPATAN_BPJS_CRUD'), 403);
        $data = $request->validate([
            'tanggal' => 'required|date',
            'jenis_bpjs' => 'required|in:REGULAR,EVAKUASI,OBAT',
            'no_sep' => 'nullable|string|max:50',
            'nama_pasien' => 'required|string|max:255',
            'ruangan_id' => 'required|exists:ruangans,id',
            'perusahaan_id' => 'nullable|exists:perusahaans,id',
            'transaksi' => 'nullable|string|max:100',
            'metode_pembayaran' => 'required|in:TUNAI,NON_TUNAI',
            'bank' => 'nullable|string|in:BRK,BSI',
            'metode_detail' => 'nullable|string|in:SETOR_TUNAI,QRIS,TRANSFER',
            'rs_tindakan' => 'nullable|numeric|min:0',
            'rs_obat' => 'nullable|numeric|min:0',
            'pelayanan_tindakan' => 'nullable|numeric|min:0',
            'pelayanan_obat' => 'nullable|numeric|min:0',
        ]);

        if ($data['jenis_bpjs'] === 'REGULAR' && empty($data['no_sep'])) {
            return response()->json(['message' => 'No SEP wajib diisi untuk BPJS Regular'], 422);
        }

        if ($data['metode_pembayaran'] === 'TUNAI') {
            $data['bank'] = 'BRK';
            $data['metode_detail'] = 'SETOR_TUNAI';
        } elseif (empty($data['bank']) || empty($data['metode_detail'])) {
            return response()->json(['message' => 'Bank dan metode detail wajib diisi untuk Non Tunai'], 422);
        }

        $data['transaksi'] = $data['transaksi'] ?? 'BPJS';
        $data['rs_tindakan'] = $data['rs_tindakan'] ?? 0;
        $data['rs_obat'] = $data['rs_obat'] ?? 0;
        $data['pelayanan_tindakan'] = $data['pelayanan_tindakan'] ?? 0;
        $data['pelayanan_obat'] = $data['pelayanan_obat'] ?? 0;
        $data['total'] = $data['rs_tindakan'] + $data['rs_obat'] + $data['pelayanan_tindakan'] + $data['pelayanan_obat'];
        $data['tahun'] = session('tahun_anggaran');

        $pendapatan = PendapatanBpjs::create($data);

        ActivityLog::log(
            'CREATE',
            'PENDAPATAN_BPJS',
            "Menambah pendapatan BPJS pasien {$pendapatan->nama_pasien}",
            $pendapatan->id,
            null,
            $pendapatan->toArray()
        );

        return response()->json(['success' => true]);
    }

    /* =========================
       UPDATE
    ========================= */
    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->hasPermission('PENDAPATAN_BPJS_CRUD'), 403);
        $pendapatan = PendapatanBpjs::findOrFail($id);

        $data = $request->validate([
            'tanggal' => 'required|date',
            'jenis_bpjs' => 'required|in:REGULAR,EVAKUASI,OBAT',
            'no_sep' => 'nullable|string|max:50',
            'nama_pasien' => 'required|string|max:255',
            'ruangan_id' => 'required|exists:ruangans,id',
            'perusahaan_id' => 'nullable|exists:perusahaans,id',
            'transaksi' => 'nullable|string|max:100',
            'metode_pembayaran' => 'required|in:TUNAI,NON_TUNAI',
            'bank' => 'nullable|string|in:BRK,BSI',
            'metode_detail' => 'nullable|string|in:SETOR_TUNAI,QRIS,TRANSFER',
            'rs_tindakan' => 'nullable|numeric|min:0',
            'rs_obat' => 'nullable|numeric|min:0',
            'pelayanan_tindakan' => 'nullable|numeric|min:0',
            'pelayanan_obat' => 'nullable|numeric|min:0',
        ]);

        if ($data['jenis_bpjs'] === 'REGULAR' && empty($data['no_sep'])) {
            return response()->json(['message' => 'No SEP wajib diisi untuk BPJS Regular'], 422);
        }

        if ($data['metode_pembayaran'] === 'TUNAI') {
            $data['bank'] = 'BRK';
            $data['metode_detail'] = 'SETOR_TUNAI';
        } elseif (empty($data['bank']) || empty($data['metode_detail'])) {
            return response()->json(['message' => 'Bank dan metode detail wajib diisi untuk Non Tunai'], 422);
        }

        $data['transaksi'] = $data['transaksi'] ?? 'BPJS';
        $data['rs_tindakan'] = $data['rs_tindakan'] ?? 0;
        $data['rs_obat'] = $data['rs_obat'] ?? 0;
        $data['pelayanan_tindakan'] = $data['pelayanan_tindakan'] ?? 0;
        $data['pelayanan_obat'] = $data['pelayanan_obat'] ?? 0;
        $data['total'] = $data['rs_tindakan'] + $data['rs_obat'] + $data['pelayanan_tindakan'] + $data['pelayanan_obat'];

        $oldValues = $pendapatan->toArray();
        $pendapatan->update($data);

        ActivityLog::log(
            'UPDATE',
            'PENDAPATAN_BPJS',
            "Mengubah pendapatan BPJS pasien {$pendapatan->nama_pasien}",
            $pendapatan->id,
            $oldValues,
            $pendapatan->toArray()
        );

        return response()->json(['success' => true]);
    }

    /* =========================
       SHOW
    ========================= */
    public function show($id)
    {
        abort_unless(auth()->user()->hasPermission('PENDAPATAN_BPJS_VIEW'), 403);
        return PendapatanBpjs::with('ruangan', 'perusahaan')->findOrFail($id);
    }

    /* =========================
       DELETE
    ========================= */
    public function destroy($id)
    {
        abort_unless(auth()->user()->hasPermission('PENDAPATAN_BPJS_CRUD'), 403);
        $pendapatan = PendapatanBpjs::findOrFail($id);
        $oldValues = $pendapatan->toArray();
        $pendapatan->delete();

        ActivityLog::log(
            'DELETE',
            'PENDAPATAN_BPJS',
            "Menghapus pendapatan BPJS pasien {$pendapatan->nama_pasien}",
            $id,
            $oldValues,
            null
        );

        return response()->json(['success' => true]);
    }

    /* =========================
       TEMPLATE & IMPORT
    ========================= */
    public function downloadTemplate()
    {
        abort_unless(auth()->user()->hasPermission('PENDAPATAN_BPJS_TEMPLATE'), 403);
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=template_pendapatan_bpjs.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $columns = [
            'Tanggal (YYYY-MM-DD)',
            'Jenis BPJS (REGULAR/EVAKUASI/OBAT)',
            'No SEP',
            'Nama Pasien',
            'Ruangan',
            'Perusahaan',
            'Metode (TUNAI/NON_TUNAI)',
            'Bank (BRK/BSI)',
            'Detail (SETOR_TUNAI/QRIS/TRANSFER)',
            'RS Tindakan',
            'RS Obat',
            'Pelayanan Tindakan',
            'Pelayanan Obat'
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, ['2026-02-15', 'REGULAR', '0001R001', 'BUDI', 'AMBULAN', 'BPJS KESEHATAN', 'NON_TUNAI', 'BRK', 'TRANSFER', '150000', '50000', '100000', '25000']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENDAPATAN_BPJS_IMPORT'), 403);
        $request->validate(['file' => 'required|mimes:csv,txt']);

        $file = $request->file('file');
        $filePath = $file->getRealPath();
        $firstLine = fgets(fopen($filePath, 'r'));
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
        $handle = fopen($filePath, 'r');

        fgetcsv($handle, 0, $delimiter); // Skip header

        $ruangans = Ruangan::all()->pluck('id', 'nama')->mapWithKeys(fn($id, $name) => [strtoupper($name) => $id]);
        $perusahaans = Perusahaan::all()->pluck('id', 'nama')->mapWithKeys(fn($id, $name) => [strtoupper($name) => $id]);

        $count = 0;
        $this->service->transaction(function () use ($handle, $delimiter, $ruangans, $perusahaans, &$count) {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                if (count($row) < 4 || empty($row[0]))
                    continue;

                $namaRuangan = strtoupper(trim($row[4] ?? ''));
                $ruanganId = $ruangans[$namaRuangan] ?? 39;
                $perusahaanId = $perusahaans[strtoupper(trim($row[5] ?? ''))] ?? null;

                $jenisBpjs = strtoupper($row[1] ?? 'REGULAR');
                if ($namaRuangan === 'AMBULAN' || $namaRuangan === 'AMBULANCE')
                    $jenisBpjs = 'EVAKUASI';
                elseif ($namaRuangan === 'APOTEK')
                    $jenisBpjs = 'OBAT';

                $rsT = $this->service->parseNumeric($row[9] ?? 0);
                $rsO = $this->service->parseNumeric($row[10] ?? 0);
                $plT = $this->service->parseNumeric($row[11] ?? 0);
                $plO = $this->service->parseNumeric($row[12] ?? 0);
                $tanggal = $this->service->parseDate($row[0]);

                if ($jenisBpjs === 'OBAT') {
                    $rsT = 0;
                    $plT = 0;
                } else {
                    $rsO = 0;
                    $plO = 0;
                }

                PendapatanBpjs::create([
                    'tanggal' => $tanggal,
                    'jenis_bpjs' => $jenisBpjs,
                    'no_sep' => $row[2] ?? null,
                    'nama_pasien' => $row[3] ?? 'Tanpa Nama',
                    'ruangan_id' => $ruanganId,
                    'perusahaan_id' => $perusahaanId,
                    'metode_pembayaran' => str_replace(' ', '_', strtoupper(trim($row[6] ?? 'TUNAI'))),
                    'bank' => strtoupper(trim($row[7] ?? 'BRK')),
                    'metode_detail' => str_replace(' ', '_', strtoupper(trim($row[8] ?? 'SETOR_TUNAI'))),
                    'rs_tindakan' => $rsT,
                    'rs_obat' => $rsO,
                    'pelayanan_tindakan' => $plT,
                    'pelayanan_obat' => $plO,
                    'total' => $rsT + $rsO + $plT + $plO,
                    'transaksi' => 'BPJS Import',
                    'tahun' => session('tahun_anggaran')
                ]);
                $count++;
            }
        });

        fclose($handle);
        ActivityLog::log('IMPORT', 'PENDAPATAN_BPJS', "Berhasil mengimpor {$count} data pendapatan BPJS", null, null, null);
        return response()->json(['success' => true, 'count' => $count]);
    }

    public function bulkDelete(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENDAPATAN_BPJS_BULK'), 403);
        $request->validate(['tanggal' => 'required|date']);

        $query = PendapatanBpjs::where('tanggal', $request->tanggal)->where('tahun', session('tahun_anggaran'));
        if ($request->jenis_bpjs)
            $query->where('jenis_bpjs', $request->jenis_bpjs);

        $count = $query->count();
        $query->delete();

        ActivityLog::log('DELETE', 'PENDAPATAN_BPJS', "Menghapus massal {$count} data pendapatan BPJS tanggal {$request->tanggal}", null, null, null);

        return response()->json(['success' => true, 'count' => $count]);
    }
}
