<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TreasurerCash;

class TreasurerCashController extends Controller
{
    protected $service;

    public function __construct(\App\Services\CashLedgerService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        // ... (existing code inside index remains the same)
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_BKU_VIEW') || auth()->user()->isAdmin(), 403);

        $year = $request->get('year', date('Y'));
        $month = $request->get('month');

        $query = TreasurerCash::whereYear('date', $year);

        $openingBalance = 0;
        if ($month) {
            $openingBalance = (float) TreasurerCash::whereYear('date', $year)
                ->whereMonth('date', '<', $month)
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->value('balance') ?? 0;

            $query->whereMonth('date', $month);
        }

        $data = $query->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->paginate($request->get('limit', 10));

        $items = $data->getCollection();

        return response()->json([
            'data' => $items,
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'total' => $data->total(),
            'from' => $data->firstItem(),
            'to' => $data->lastItem(),
            'opening_balance' => $openingBalance,
            'summary' => [
                'total_debit' => (float) $items->sum('debit'),
                'total_credit' => (float) $items->sum('credit'),
                'final_balance' => (float) ($items->last() ? $items->last()->balance : $openingBalance)
            ]
        ]);
    }

    public function sync(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_BKU_SYNC') || auth()->user()->isAdmin(), 403);
        $year = $request->get('year', session('tahun_anggaran', date('Y')));
        $this->service->syncLedger($year);
        return response()->json(['message' => 'BKU synchronized successfully']);
    }
}
