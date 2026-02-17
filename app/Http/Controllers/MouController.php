<?php

namespace App\Http\Controllers;

use App\Models\Mou;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MouController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasPermission('MASTER_VIEW'), 403);

        return view('dashboard.pages.mou');
    }

    /* ================= CRUD (ADMIN) ================= */

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('MASTER_CRUD'), 403);

        $request->validate([
            'nama' => 'required|string|max:100',
        ]);

        Mou::create([
            'kode' => $this->generateKodeMou(),
            'nama' => $request->nama,
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, Mou $mou)
    {
        abort_unless(auth()->user()->hasPermission('MASTER_CRUD'), 403);

        $mou->update(
            $request->validate([
                'nama' => 'required|string|max:100',
            ])
        );

        return response()->json(['success' => true]);
    }

    public function destroy(Mou $mou)
    {
        abort_unless(auth()->user()->hasPermission('MASTER_CRUD'), 403);

        $mou->delete();

        return response()->json(['success' => true]);
    }

    /* ================= DATA JSON ================= */

    public function list()
    {
        return response()->json(
            Mou::select('id', 'kode', 'nama')
                ->orderByRaw("CAST(SUBSTRING(kode, 4) AS UNSIGNED)")
                ->get()
        );
    }

    public function nextKode()
    {
        return response()->json([
            'kode' => $this->generateKodeMou()
        ]);
    }

    /* ================= HELPER ================= */

    private function generateKodeMou()
    {
        $last = Mou::orderByRaw("CAST(SUBSTRING(kode, 4) AS UNSIGNED) DESC")
            ->first();

        if (!$last)
            return 'MOU001';

        $num = (int) substr($last->kode, 3);
        return 'MOU' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
    }
}
