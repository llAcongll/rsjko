<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerusahaanController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasPermission('MASTER_PERUSAHAAN_VIEW') || auth()->user()->hasPermission('MASTER_VIEW'), 403);

        return view('dashboard.pages.perusahaan');
    }

    /* ================= CRUD (ADMIN) ================= */

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('MASTER_PERUSAHAAN_CRUD') || auth()->user()->hasPermission('MASTER_CRUD'), 403);

        $request->validate([
            'nama' => 'required|string|max:100',
        ]);

        Perusahaan::create([
            'kode' => $this->generateKodePerusahaan(),
            'nama' => $request->nama,
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, Perusahaan $perusahaan)
    {
        abort_unless(auth()->user()->hasPermission('MASTER_PERUSAHAAN_CRUD') || auth()->user()->hasPermission('MASTER_CRUD'), 403);

        $perusahaan->update(
            $request->validate([
                'nama' => 'required|string|max:100',
            ])
        );

        return response()->json(['success' => true]);
    }

    public function destroy(Perusahaan $perusahaan)
    {
        abort_unless(auth()->user()->hasPermission('MASTER_CRUD'), 403);

        $perusahaan->delete();

        return response()->json(['success' => true]);
    }

    /* ================= DATA JSON ================= */

    public function list()
    {
        return response()->json(
            Perusahaan::select('id', 'kode', 'nama')
                ->orderByRaw("CAST(SUBSTRING(kode, 4) AS UNSIGNED)")
                ->get()
        );
    }

    public function nextKode()
    {
        return response()->json([
            'kode' => $this->generateKodePerusahaan()
        ]);
    }

    /* ================= HELPER ================= */

    private function generateKodePerusahaan()
    {
        $last = Perusahaan::orderByRaw("CAST(SUBSTRING(kode, 4) AS UNSIGNED) DESC")
            ->first();

        if (!$last)
            return 'PRS001';

        $num = (int) substr($last->kode, 3);
        return 'PRS' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
    }
}
