<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PendapatanKerjasama;
use App\Models\Ruangan;
use App\Models\Mou;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PendapatanKerjasamaController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENDAPATAN_KERJA_VIEW'), 403);
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');
        $query = PendapatanKerjasama::with('ruangan', 'mou')
            ->where('tahun', session('tahun_anggaran'))
            ->orderBy('tanggal', 'asc');
        if ($search) {
            $query->where(function ($q) use ($search) {
                // Try to handle Indonesian date format d/m/Y
                $dateSearch = $search;
                if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $search)) {
                    try {
                        $dateSearch = Carbon::createFromFormat('d/m/Y', $search)->format('Y-m-d');
                    } catch (\Exception $e) {
                        // ignore invalid date
                    }
                }

                $q->where('nama_pasien', 'like', "%{$search}%")
                    ->orWhere('transaksi', 'like', "%{$search}%")
                    ->orWhereDate('tanggal', '=', $dateSearch) // Exact date match
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

            return response()->json([
                'data' => $paginated->items(),
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem(),
                'total' => $paginated->total(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'aggregates' => [
                    'total_rs' => $totals->total_rs ?? 0,
                    'total_pelayanan' => $totals->total_pelayanan ?? 0,
                    'total_all' => $totals->grand_total ?? 0,
                ]
            ]);
        }
        return $query->paginate($perPage);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENDAPATAN_KERJA_CRUD'), 403);
        $data = $request->validate([
            'tanggal' => 'required|date',
            'nama_pasien' => 'required|string|max:255',
            'ruangan_id' => 'required|exists:ruangans,id',
            'mou_id' => 'nullable|exists:mous,id',
            'transaksi' => 'nullable|string|max:150',
            'metode_pembayaran' => 'required|in:TUNAI,NON_TUNAI',
            'bank' => 'nullable|string|in:BRK,BSI',
            'metode_detail' => 'nullable|string|in:SETOR_TUNAI,QRIS,TRANSFER',
            'rs_tindakan' => 'nullable|numeric|min:0',
            'rs_obat' => 'nullable|numeric|min:0',
            'pelayanan_tindakan' => 'nullable|numeric|min:0',
            'pelayanan_obat' => 'nullable|numeric|min:0',
        ]);
        if ($data['metode_pembayaran'] === 'TUNAI') {
            $data['bank'] = 'BRK';
            $data['metode_detail'] = 'SETOR_TUNAI';
        }
        $data['total'] = ($data['rs_tindakan'] ?? 0) + ($data['rs_obat'] ?? 0) + ($data['pelayanan_tindakan'] ?? 0) +
            ($data['pelayanan_obat'] ?? 0);
        $data['tahun'] = session('tahun_anggaran');
        PendapatanKerjasama::create($data);
        return response()->json(['success' => true]);
    }

    public function show($id)
    {
        abort_unless(auth()->user()->hasPermission('PENDAPATAN_KERJA_VIEW'), 403);
        return PendapatanKerjasama::with('ruangan', 'mou')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->hasPermission('PENDAPATAN_KERJA_CRUD'), 403);
        $pendapatan = PendapatanKerjasama::findOrFail($id);
        $data = $request->validate([
            'tanggal' => 'required|date',
            'nama_pasien' => 'required|string|max:255',
            'ruangan_id' => 'required|exists:ruangans,id',
            'mou_id' => 'nullable|exists:mous,id',
            'transaksi' => 'nullable|string|max:150',
            'metode_pembayaran' => 'required|in:TUNAI,NON_TUNAI',
            'bank' => 'nullable|string|in:BRK,BSI',
            'metode_detail' => 'nullable|string|in:SETOR_TUNAI,QRIS,TRANSFER',
            'rs_tindakan' => 'nullable|numeric|min:0',
            'rs_obat' => 'nullable|numeric|min:0',
            'pelayanan_tindakan' => 'nullable|numeric|min:0',
            'pelayanan_obat' => 'nullable|numeric|min:0',
        ]);
        if ($data['metode_pembayaran'] === 'TUNAI') {
            $data['bank'] = 'BRK';
            $data['metode_detail'] = 'SETOR_TUNAI';
        }
        $data['total'] = ($data['rs_tindakan'] ?? 0) + ($data['rs_obat'] ?? 0) + ($data['pelayanan_tindakan'] ?? 0) +
            ($data['pelayanan_obat'] ?? 0);
        $pendapatan->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->hasPermission('PENDAPATAN_KERJA_CRUD'), 403);
        PendapatanKerjasama::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function downloadTemplate()
    {
        abort_unless(auth()->user()->hasPermission('PENDAPATAN_KERJA_TEMPLATE'), 403);
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=template_pendapatan_kerjasama.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $columns = [
            'Tanggal (YYYY-MM-DD)',
            'Nama Pasien',
            'Ruangan',
            'MOU/Instansi',
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
            fputcsv($file, [
                '2026-02-15',
                'BUDI KERJASAMA',
                'FISIOTERAPI',
                'MOU PERUSAHAAN A',
                'NON_TUNAI',
                'BRK',
                'TRANSFER',
                '300000',
                '150000',
                '200000',
                '75000'
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENDAPATAN_KERJA_IMPORT'), 403);
        $request->validate(['file' => 'required|mimes:csv,txt']);
        $file = $request->file('file');
        $filePath = $file->getRealPath();

        $firstLine = fgets(fopen($filePath, 'r'));
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';

        $handle = fopen($filePath, 'r');
        fgetcsv($handle, 0, $delimiter); // Skip header

        $ruangans = Ruangan::all()->pluck('id', 'nama')->mapWithKeys(fn($id, $name) => [strtoupper($name) => $id]);
        $mous = Mou::all()->pluck('id', 'nama')->mapWithKeys(fn($id, $name) => [strtoupper($name) => $id]);

        $count = 0;
        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                if (count($row) < 4 || empty($row[0]))
                    continue;
                $namaRuangan = strtoupper(trim($row[2] ?? ''));
                $ruanganId = $ruangans[$namaRuangan] ?? 39;
                $namaMou = strtoupper(trim($row[3] ?? ''));
                $mouId = $mous[$namaMou] ?? null;
                $tanggalStr = trim($row[0]);
                try {
                    $tanggal = str_contains($tanggalStr, '/') ? Carbon::createFromFormat(
                        'd/m/Y',
                        $tanggalStr
                    )->format('Y-m-d')
                        : Carbon::parse($tanggalStr)->format('Y-m-d');
                } catch (\Exception $e) {
                    $tanggal = $tanggalStr;
                }

                $parseNumeric = function ($v) {
                    if (empty($v))
                        return 0;
                    $v = preg_replace('/[^-0-9,.]/', '', $v);
                    $latC = strrpos($v, ',');
                    $latD = strrpos($v, '.');
                    if ($latC !== false && $latD !== false) {
                        return ($latC > $latD) ? (float) str_replace(',', '.', str_replace('.', '', $v)) : (float) str_replace(',', '', $v);
                    }
                    if ($latC !== false)
                        return (strlen($v) - $latC === 4) ? (float) str_replace(',', '', $v) : (float) str_replace(',', '.', $v);
                    if ($latD !== false)
                        return (strlen($v) - $latD === 4) ? (float) str_replace('.', '', $v) : (float) $v;
                    return (float) $v;
                };

                $rsT = $parseNumeric($row[7] ?? 0);
                $rsO = $parseNumeric($row[8] ?? 0);
                $plT = $parseNumeric($row[9] ?? 0);
                $plO = $parseNumeric($row[10] ?? 0);

                $metode = str_replace(' ', '_', strtoupper(trim($row[4] ?? 'TUNAI')));
                $bank = strtoupper(trim($row[5] ?? 'BRK'));
                $detail = str_replace(' ', '_', strtoupper(trim($row[6] ?? 'SETOR_TUNAI')));

                PendapatanKerjasama::create([
                    'tanggal' => $tanggal,
                    'nama_pasien' => $row[1] ?? 'Pasien Kerjasama',
                    'ruangan_id' => $ruanganId,
                    'mou_id' => $mouId,
                    'transaksi' => $namaMou ?: 'Kerjasama Import',
                    'metode_pembayaran' => $metode,
                    'bank' => $bank,
                    'metode_detail' => $detail,
                    'rs_tindakan' => $rsT,
                    'rs_obat' => $rsO,
                    'pelayanan_tindakan' => $plT,
                    'pelayanan_obat' => $plO,
                    'total' => $rsT + $rsO + $plT + $plO,
                    'tahun' => session('tahun_anggaran')
                ]);
                $count++;
            }
            DB::commit();
            fclose($handle);
            return response()->json(['success' => true, 'count' => $count]);
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENDAPATAN_KERJA_BULK'), 403);
        $request->validate(['tanggal' => 'required|date']);
        $count = PendapatanKerjasama::where('tanggal', $request->tanggal)
            ->where('tahun', session('tahun_anggaran'))
            ->delete();
        return response()->json(['success' => true, 'count' => $count]);
    }
}