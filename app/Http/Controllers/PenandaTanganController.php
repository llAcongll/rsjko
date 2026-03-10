<?php

namespace App\Http\Controllers;

use App\Models\PenandaTangan;
use Illuminate\Http\Request;

class PenandaTanganController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasPermission('PENANDATANGAN_VIEW'), 403);

        return view('dashboard.pages.penanda_tangan');
    }

    /* ================= CRUD (ADMIN) ================= */

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENANDATANGAN_MANAGE'), 403);

        $validated = $request->validate([
            'jabatan' => 'required|string|max:100',
            'pangkat' => 'nullable|string|max:100',
            'nama' => 'required|string|max:100',
            'nip' => 'nullable|string|max:30',
        ]);

        PenandaTangan::create($validated);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, PenandaTangan $penandaTangan)
    {
        abort_unless(auth()->user()->hasPermission('PENANDATANGAN_MANAGE'), 403);

        $validated = $request->validate([
            'jabatan' => 'required|string|max:100',
            'pangkat' => 'nullable|string|max:100',
            'nama' => 'required|string|max:100',
            'nip' => 'nullable|string|max:30',
        ]);

        $penandaTangan->update($validated);

        return response()->json(['success' => true]);
    }

    public function destroy(PenandaTangan $penandaTangan)
    {
        abort_unless(auth()->user()->hasPermission('PENANDATANGAN_MANAGE'), 403);

        $penandaTangan->delete();

        return response()->json(['success' => true]);
    }

    /* ================= DATA JSON ================= */

    public function list()
    {
        return response()->json(
            PenandaTangan::orderBy('id', 'asc')->get()
        );
    }
}





