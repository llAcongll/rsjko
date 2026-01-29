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
        abort_unless(Auth::user()->isAdmin(), 403);

        return response()->json(
            Ruangan::select('id','kode','nama')
                ->orderByRaw("CAST(SUBSTRING(kode, 2) AS UNSIGNED)")
                ->get()
        );
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

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
        abort_unless(Auth::user()->isAdmin(), 403);

        $data = $request->validate([
            'nama' => 'required|string|max:100',
        ]);

        $ruangan->update($data);

        return response()->json(['success' => true]);
    }

    public function destroy(Ruangan $ruangan)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $ruangan->delete();

        return response()->json(['success' => true]);
    }

    private function generateKodeRuangan()
    {
        $last = DB::table('ruangans')
            ->select('kode')
            ->orderByRaw("CAST(SUBSTRING(kode, 2) AS UNSIGNED) DESC")
            ->first();

        if (!$last) return 'R001';

        $num = (int) substr($last->kode, 1);
        return 'R' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
    }

    public function nextKode()
    {
        return response()->json([
            'kode' => $this->generateKodeRuangan()
        ]);
    }

    public function list()
{
    return response()->json(
        Ruangan::select('id','kode','nama')
            ->orderByRaw("CAST(SUBSTRING(kode, 2) AS UNSIGNED)")
            ->get()
    );
}

}
