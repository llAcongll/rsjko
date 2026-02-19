<?php

namespace App\Http\Controllers;

use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RuanganController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasPermission('MASTER_RUANGAN_VIEW') || auth()->user()->hasPermission('MASTER_VIEW'), 403);

        return view('dashboard.pages.ruangan');
    }

    /* ================= CRUD (ADMIN) ================= */

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('MASTER_RUANGAN_CRUD') || auth()->user()->hasPermission('MASTER_CRUD'), 403);

        $request->validate([
            'nama' => 'required|string|max:100',
        ]);

        Ruangan::create([
            'kode' => $this->generateKodeRuangan(),
            'nama' => $request->nama,
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, Ruangan $ruangan)
    {
        abort_unless(auth()->user()->hasPermission('MASTER_RUANGAN_CRUD') || auth()->user()->hasPermission('MASTER_CRUD'), 403);

        $ruangan->update(
            $request->validate([
                'nama' => 'required|string|max:100',
            ])
        );

        return response()->json(['success' => true]);
    }

    public function destroy(Ruangan $ruangan)
    {
        abort_unless(auth()->user()->hasPermission('MASTER_CRUD'), 403);

        $ruangan->delete();

        return response()->json(['success' => true]);
    }

    /* ================= DATA JSON ================= */

    public function list()
    {
        return response()->json(
            Ruangan::select('id', 'kode', 'nama')
                ->orderByRaw("CAST(SUBSTRING(kode, 2) AS UNSIGNED)")
                ->get()
        );
    }

    public function nextKode()
    {
        return response()->json([
            'kode' => $this->generateKodeRuangan()
        ]);
    }

    /* ================= HELPER ================= */

    private function generateKodeRuangan()
    {
        $last = Ruangan::orderByRaw("CAST(SUBSTRING(kode, 2) AS UNSIGNED) DESC")
            ->first();

        if (!$last)
            return 'R001';

        $num = (int) substr($last->kode, 1);
        return 'R' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
    }
}
