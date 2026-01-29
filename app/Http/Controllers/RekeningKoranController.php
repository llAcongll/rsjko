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
    abort_unless(Auth::check() && Auth::user()->isAdmin(), 403);

    $q = RekeningKoran::query();

    if ($request->filled('bank') && $request->bank !== 'Semua Bank') {
        $q->where('bank', $request->bank);
    }

    if ($request->filled('start')) {
        $q->whereDate('tanggal', '>=', $request->start);
    }

    if ($request->filled('end')) {
        $q->whereDate('tanggal', '<=', $request->end);
    }

    return response()->json(
        $q->orderBy('tanggal')
          ->orderBy('id')
          ->get()
    );
}

    public function store(Request $request)
    {
        abort_unless(Auth::check() && Auth::user()->isAdmin(), 403);

        $data = $request->validate([
            'tanggal'     => 'required|date',
            'bank'        => ['required', 'string', Rule::in($this->banks())],
            'keterangan'  => 'required|string|max:255',
            'cd'          => 'required|in:C,D',
            'jumlah'      => 'required|numeric|min:0',
        ]);

        RekeningKoran::create($data);

        return response()->json(['success' => true]);
    }

    public function show(RekeningKoran $rekeningKoran)
    {
        abort_unless(Auth::check() && Auth::user()->isAdmin(), 403);
        return response()->json($rekeningKoran);
    }

    public function update(Request $request, RekeningKoran $rekeningKoran)
    {
        abort_unless(Auth::check() && Auth::user()->isAdmin(), 403);

        $data = $request->validate([
            'tanggal'     => 'required|date',
            'bank'        => ['required', 'string', Rule::in($this->banks())],
            'keterangan'  => 'required|string|max:255',
            'cd'          => 'required|in:C,D',
            'jumlah'      => 'required|numeric|min:0',
        ]);

        $rekeningKoran->update($data);

        return response()->json(['success' => true]);
    }

    public function destroy(RekeningKoran $rekeningKoran)
    {
        abort_unless(Auth::check() && Auth::user()->isAdmin(), 403);

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

}
