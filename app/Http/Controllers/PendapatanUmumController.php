<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PendapatanUmum;

class PendapatanUmumController extends Controller
{
    /**
     * Ambil data pendapatan umum
     * (dipakai untuk render tabel via AJAX)
     */
    public function index()
    {
        return PendapatanUmum::with('ruangan')
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    /**
     * Simpan pendapatan umum
     */
    public function store(Request $request)
    {
        // =========================
        // VALIDASI DASAR
        // =========================
        $data = $request->validate([
            'tanggal' => 'required|date',
            'nama_pasien' => 'required|string|max:255',
            'ruangan_id' => 'required|exists:ruangans,id',

            'metode_pembayaran' => 'required|in:TUNAI,NON_TUNAI',

            // nominal (boleh kosong → default 0)
            'rs_tindakan' => 'nullable|numeric|min:0',
            'rs_obat' => 'nullable|numeric|min:0',
            'pelayanan_tindakan' => 'nullable|numeric|min:0',
            'pelayanan_obat' => 'nullable|numeric|min:0',
        ]);

        // =========================
        // VALIDASI KONDISIONAL
        // =========================
        if ($request->metode_pembayaran === 'NON_TUNAI') {
            $request->validate([
                'bank_id' => 'required|exists:banks,id',
                'metode_detail' => 'required|string|max:50',
            ]);

            $data['bank_id'] = $request->bank_id;
            $data['metode_detail'] = $request->metode_detail;
        } else {
            // TUNAI → pastikan null
            $data['bank_id'] = null;
            $data['metode_detail'] = null;
        }

        // =========================
        // NORMALISASI NILAI NOMINAL
        // =========================
        $data['rs_tindakan'] = $data['rs_tindakan'] ?? 0;
        $data['rs_obat'] = $data['rs_obat'] ?? 0;
        $data['pelayanan_tindakan'] = $data['pelayanan_tindakan'] ?? 0;
        $data['pelayanan_obat'] = $data['pelayanan_obat'] ?? 0;

        // =========================
        // HITUNG TOTAL
        // =========================
        $data['total'] =
            $data['rs_tindakan'] +
            $data['rs_obat'] +
            $data['pelayanan_tindakan'] +
            $data['pelayanan_obat'];

        // =========================
        // SIMPAN
        // =========================
        PendapatanUmum::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Pendapatan umum berhasil disimpan',
        ]);
    }

    public function destroy($id)
    {
        PendapatanUmum::findOrFail($id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Pendapatan berhasil dihapus'
        ]);
    }

    public function show($id)
    {
        return PendapatanUmum::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'tanggal' => 'required|date',
            'nama_pasien' => 'required',
            'ruangan_id' => 'required',
        ]);

        PendapatanUmum::findOrFail($id)->update($data);

        return response()->json(['success' => true]);
    }

}
