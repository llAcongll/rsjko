<?php

namespace App\Http\Controllers;

use App\Models\AnggaranRekening;
use App\Models\AnggaranRincian;
use App\Models\KodeRekening;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnggaranRekeningController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'kode_rekening_id' => 'required|exists:kode_rekening,id',
            'tahun' => 'required|integer|min:2000|max:2100',
            'nilai' => 'nullable|numeric|min:0',
            'rincian' => 'nullable|array',
            'rincian.*.uraian' => 'required|string',
            'rincian.*.volume' => 'required|numeric|min:0',
            'rincian.*.satuan' => 'required|string',
            'rincian.*.tarif' => 'required|numeric|min:0',
        ]);

        $rekening = KodeRekening::findOrFail($data['kode_rekening_id']);

        if ($rekening->tipe !== 'detail') {
            return response()->json('Anggaran hanya boleh untuk rekening DETAIL', 422);
        }

        return DB::transaction(function () use ($data) {
            $totalNilai = 0;
            $rincianData = $data['rincian'] ?? [];

            if (!empty($rincianData)) {
                foreach ($rincianData as &$item) {
                    $item['subtotal'] = $item['volume'] * $item['tarif'];
                    $totalNilai += $item['subtotal'];
                }
            } else {
                $totalNilai = $data['nilai'] ?? 0;
            }

            $anggaran = AnggaranRekening::updateOrCreate(
                ['kode_rekening_id' => $data['kode_rekening_id'], 'tahun' => $data['tahun']],
                ['nilai' => $totalNilai]
            );

            // Sync Rincian
            $anggaran->rincian()->delete();
            if (!empty($rincianData)) {
                foreach ($rincianData as $item) {
                    $anggaran->rincian()->create($item);
                }
            }

            return response()->json(['status' => 'ok', 'total' => $totalNilai]);
        });
    }

    public function showRincian($rekening_id, $tahun)
    {
        $anggaran = AnggaranRekening::with('rincian')
            ->where('kode_rekening_id', $rekening_id)
            ->where('tahun', $tahun)
            ->first();

        return response()->json($anggaran);
    }
}
