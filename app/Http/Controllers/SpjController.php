<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Spj;
use App\Models\SpjItem;
use App\Models\Expenditure;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SpjController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_SPJ') || auth()->user()->isAdmin(), 403);

        $search = $request->get('search');
        $limit = $request->get('limit', 10);

        $query = Spj::with(['bendahara', 'items.expenditure']);

        if ($search) {
            $query->where('spj_number', 'like', "%$search%");
        }

        return response()->json($query->orderBy('spj_date', 'desc')->paginate($limit));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_SPJ') || auth()->user()->isAdmin(), 403);

        $data = $request->validate([
            'spj_number' => 'required|string|max:100|unique:spj,spj_number',
            'spj_date' => 'required|date',
            'bendahara_id' => 'required|exists:users,id',
            'expenditure_ids' => 'required|array',
            'expenditure_ids.*' => 'exists:expenditures,id',
        ]);

        return DB::transaction(function () use ($data) {
            $year = \Carbon\Carbon::parse($data['spj_date'])->year;
            $activeSiklus = app(\App\Services\SiklusService::class)->getActiveSiklus($year);

            $spj = Spj::create([
                'spj_number' => $data['spj_number'],
                'spj_date' => $data['spj_date'],
                'bendahara_id' => $data['bendahara_id'],
                'siklus_up' => $activeSiklus,
                'status' => 'DRAFT'
            ]);

            foreach ($data['expenditure_ids'] as $expId) {
                SpjItem::create([
                    'spj_id' => $spj->id,
                    'expenditure_id' => $expId
                ]);
            }

            \App\Models\ActivityLog::log(
                'CREATE',
                'SPJ',
                "Membuat SPJ: {$spj->spj_number}",
                $spj->id,
                null,
                $spj->toArray()
            );

            return response()->json($spj->load('items'), 201);
        });
    }

    public function show($id)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_SPJ') || auth()->user()->isAdmin(), 403);
        return response()->json(Spj::with(['bendahara', 'items.expenditure.kodeRekening'])->findOrFail($id));
    }

    public function updateStatus(Request $request, $id)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_SPJ') || auth()->user()->isAdmin(), 403);

        $data = $request->validate([
            'status' => 'required|in:DRAFT,SUBMITTED,VALID'
        ]);

        $spj = Spj::findOrFail($id);
        $spj->update(['status' => $data['status']]);

        return response()->json($spj);
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_SPJ') || auth()->user()->isAdmin(), 403);
        try {
            $spj = Spj::findOrFail($id);

            // AUDIT SAFETY: Only DRAFT SPJs can be deleted
            if ($spj->status !== 'DRAFT') {
                throw new \Exception("Hanya SPJ dengan status DRAFT yang bisa dihapus.");
            }

            $oldValues = $spj->toArray();
            $spjNumber = $spj->spj_number;
            $spj->delete();

            \App\Models\ActivityLog::log(
                'DELETE',
                'SPJ',
                "Menghapus SPJ: {$spjNumber}",
                $id,
                $oldValues,
                null
            );

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get list of expenditures that haven't been linked to an SPJ yet.
     */
    public function getUnlinkedExpenditures()
    {
        $linkedIds = SpjItem::pluck('expenditure_id')->toArray();

        $unlinked = Expenditure::with('kodeRekening')
            ->where('spending_type', 'UP')
            ->whereNotIn('id', $linkedIds)
            ->get();

        return response()->json($unlinked);
    }

    public function print($id)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_SPJ') || auth()->user()->isAdmin(), 403);
        $spj = Spj::with(['bendahara', 'items.expenditure.kodeRekening'])->findOrFail($id);

        // Sanitize filename: replace / and \ with - as they are invalid in filenames
        $safeNumber = str_replace(['/', '\\'], '-', $spj->spj_number);

        $pdf = Pdf::loadView('dashboard.pages.pengeluaran.print-spj', compact('spj'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("SPJ-{$safeNumber}.pdf");
    }
}
