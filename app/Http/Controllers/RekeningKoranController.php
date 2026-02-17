<?php

namespace App\Http\Controllers;

use App\Models\RekeningKoran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RekeningKoranController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->hasPermission('REKENING_VIEW'), 403);

        $q = RekeningKoran::query()
            ->where('tahun', session('tahun_anggaran'));

        if ($request->filled('bank') && $request->bank !== 'Semua Bank') {
            $q->where('bank', $request->bank);
        }

        if ($request->filled('start')) {
            $q->whereDate('tanggal', '>=', $request->start);
        }

        if ($request->filled('end')) {
            $q->whereDate('tanggal', '<=', $request->end);
        }

        $q->orderBy('tanggal')->orderBy('id');

        // Support pagination jika ada per_page
        $perPage = $request->get('per_page');
        if ($perPage) {
            return response()->json($q->paginate((int) $perPage));
        }

        return response()->json($q->get());
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->hasPermission('REKENING_CRUD'), 403);

        $data = $request->validate([
            'tanggal' => 'required|date',
            'bank' => ['required', 'string', Rule::in($this->banks())],
            'keterangan' => 'required|string|max:255',
            'cd' => 'required|in:C,D',
            'jumlah' => 'required|numeric|min:0',
        ]);

        $data['tahun'] = session('tahun_anggaran');
        RekeningKoran::create($data);

        return response()->json(['success' => true]);
    }

    public function show(RekeningKoran $rekeningKoran)
    {
        abort_unless(Auth::user()->hasPermission('REKENING_VIEW'), 403);
        return response()->json($rekeningKoran);
    }

    public function update(Request $request, RekeningKoran $rekeningKoran)
    {
        abort_unless(Auth::user()->hasPermission('REKENING_CRUD'), 403);

        $data = $request->validate([
            'tanggal' => 'required|date',
            'bank' => ['required', 'string', Rule::in($this->banks())],
            'keterangan' => 'required|string|max:255',
            'cd' => 'required|in:C,D',
            'jumlah' => 'required|numeric|min:0',
        ]);

        $rekeningKoran->update($data);

        return response()->json(['success' => true]);
    }

    public function destroy(RekeningKoran $rekeningKoran)
    {
        abort_unless(Auth::user()->hasPermission('REKENING_CRUD'), 403);

        $rekeningKoran->delete();

        return response()->json(['success' => true]);
    }

    private function banks()
    {
        return [
            'Bank Riau Kepri Syariah',
            'Bank Syariah Indonesia',
        ];
    }

    public function downloadTemplate()
    {
        abort_unless(Auth::user()->hasPermission('REKENING_TEMPLATE'), 403);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_rekening_koran.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Tanggal', 'Uraian', 'C/D (C=Masuk, D=Keluar)', 'Jumlah', 'Bank']);
            fputcsv($file, [1, date('Y-m-d'), 'Contoh Transaksi', 'C', 1000000, 'Bank Riau Kepri Syariah']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        abort_unless(Auth::user()->hasPermission('REKENING_IMPORT'), 403);

        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        // Detect separator
        $line = fgets(fopen($file->getRealPath(), 'r'));
        $delimiter = strpos($line, ';') !== false ? ';' : ',';

        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle, 0, $delimiter); // Skip header with correct delimiter

        $count = 0;
        $errors = [];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            // Mapping: 0=No, 1=Tanggal, 2=Uraian, 3=CD, 4=Jumlah, 5=Bank
            if (count($row) < 6)
                continue;

            $tanggal = $row[1];
            $uraian = $row[2];
            $cd = strtoupper(trim($row[3]));

            // Clean number format (remove dots, replace comma decimal with dot)
            // Example: 1.000.000 -> 1000000 | 1,000,000.00 -> 1000000.00
            $jumlahRaw = $row[4];

            // Remove thousands separator (dot) if any, then replace decimal comma with dot
            if (strpos($jumlahRaw, '.') !== false && strpos($jumlahRaw, ',') !== false) {
                // Complex format like 1.000,00 or 1,000.00
                // Assume standard Indo: dot as thousand, comma as decimal
                $jumlahRaw = str_replace('.', '', $jumlahRaw);
                $jumlahRaw = str_replace(',', '.', $jumlahRaw);
            } elseif (strpos($jumlahRaw, '.') !== false) {
                // Could be 10000.00 (US) or 10.000 (Indo integer)
                // Simple heuristic: if dot is 3 chars from end, maybe decimal? 
                // For safety in this context, let's assume raw numbers or standard English
                // BUT user might use Excel default which depends on locale.
                // Let's just strip non-numeric/dot/comma
            }

            // Robust cleanup: keep only digits, dots, commas, minus
            $jumlahClean = preg_replace('/[^0-9.,-]/', '', $jumlahRaw);

            // Try to guess locale
            // If it has comma and dot: 1.234,56 -> remove dot, comma to dot
            if (strpos($jumlahClean, '.') !== false && strpos($jumlahClean, ',') !== false) {
                $jumlahClean = str_replace('.', '', $jumlahClean);
                $jumlahClean = str_replace(',', '.', $jumlahClean);
            }
            // If only comma: 1234,56 -> comma to dot
            elseif (strpos($jumlahClean, ',') !== false) {
                $jumlahClean = str_replace(',', '.', $jumlahClean);
            }

            $jumlah = (float) $jumlahClean;
            $bank = trim($row[5]);

            // Fix date format: 10/1/2026 -> 10-1-2026 to force DD-MM-YYYY
            $tanggalFixed = str_replace('/', '-', $tanggal);

            // Simple validation
            if (!strtotime($tanggalFixed)) {
                $errors[] = "Baris " . ($count + 2) . ": Format tanggal salah ($tanggal)";
                continue;
            }
            if (!in_array($cd, ['C', 'D'])) {
                $errors[] = "Baris " . ($count + 2) . ": CD harus C atau D";
                continue;
            }
            if (!in_array($bank, $this->banks())) {
                $errors[] = "Baris " . ($count + 2) . ": Bank tidak valid. Gunakan: " . implode(', ', $this->banks());
                continue;
            }

            RekeningKoran::create([
                'tahun' => session('tahun_anggaran'), // Asumsi tahun sesuai sesi
                'tanggal' => date('Y-m-d', strtotime($tanggalFixed)),
                'bank' => $bank,
                'keterangan' => $uraian,
                'cd' => $cd,
                'jumlah' => $jumlah
            ]);

            $count++;
        }

        fclose($handle);

        return response()->json([
            'success' => true,
            'message' => "Berhasil mengimpor $count data.",
            'errors' => $errors
        ]);
    }
    public function bulkDelete(Request $request)
    {
        abort_unless(Auth::user()->hasPermission('REKENING_BULK'), 403);

        $bank = $request->input('bank');
        $start = $request->input('start');
        $end = $request->input('end');
        $tahun = session('tahun_anggaran');

        $query = RekeningKoran::where('tahun', $tahun);

        if ($bank && $bank !== 'Semua Bank') {
            $query->where('bank', $bank);
        }
        if ($start) {
            $query->whereDate('tanggal', '>=', $start);
        }
        if ($end) {
            $query->whereDate('tanggal', '<=', $end);
        }

        $count = $query->count();
        $query->delete();

        return response()->json($count);
    }
}
