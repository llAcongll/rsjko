<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasPermission('SAFE_SPACE_VIEW'), 403);

        return view('dashboard.pages.schools');
    }

    /* ================= CRUD ================= */

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('MASTER_MANAGE'), 403);

        $data = $request->validate([
            'code' => 'required|string|max:50|unique:schools,code',
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        School::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Sekolah berhasil ditambahkan.'
        ]);
    }

    public function update(Request $request, School $school)
    {
        abort_unless(auth()->user()->hasPermission('MASTER_MANAGE'), 403);

        $data = $request->validate([
            'code' => 'required|string|max:50|unique:schools,code,' . $school->id,
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $school->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Sekolah berhasil diperbarui.'
        ]);
    }

    public function destroy(School $school)
    {
        abort_unless(auth()->user()->hasPermission('MASTER_MANAGE'), 403);

        // Jangan menghapus sekolah yang sudah memiliki data skrining.
        if ($school->screenings()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Sekolah tidak dapat dihapus karena sudah memiliki data skrining. Silakan nonaktifkan sekolah.'
            ], 422);
        }

        $school->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sekolah berhasil dihapus.'
        ]);
    }

    /* ================= DATA JSON ================= */

    public function list()
    {
        abort_unless(auth()->user()->hasPermission('SAFE_SPACE_VIEW'), 403);

        return response()->json(
            School::select('id', 'code', 'name', 'is_active')
                ->orderBy('name')
                ->get()
        );
    }

    /* ================= DATA UNTUK BOT ================= */

    public function activeSchools()
    {
        return response()->json(
            School::select('id', 'code', 'name')
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
        );
    }
}
