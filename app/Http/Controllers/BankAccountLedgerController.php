<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankAccountLedger;
use App\Services\BankLedgerService;
use Carbon\Carbon;

class BankAccountLedgerController extends Controller
{
    protected $service;

    public function __construct(BankLedgerService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_CAIR') || auth()->user()->isAdmin(), 403);

        $month = $request->get('month', date('n'));
        $year = $request->get('year', date('Y'));

        $query = BankAccountLedger::whereYear('date', $year);
        if ($month) {
            $query->whereMonth('date', $month);
        }

        $data = $query->orderBy('date', 'asc')->orderBy('id', 'asc')->get();

        $currentBalance = $this->service->getCurrentBalance();

        return response()->json([
            'data' => $data,
            'current_balance' => $currentBalance
        ]);
    }

    public function deposit(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_CAIR') || auth()->user()->isAdmin(), 403);

        $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255'
        ]);

        $this->service->recordEntry(
            $request->date,
            'DEPOSIT_MANUAL',
            $request->amount,
            'deposit_manual',
            time(),
            'DEBIT',
            $request->description
        );

        // Also record in BKU (Treasurer Cash) as Transfer Penerimaan
        app(\App\Services\CashLedgerService::class)->recordEntry(
            $request->date,
            'DEPOSIT_MANUAL',
            $request->amount,
            'deposit_manual',
            time(),
            'DEBIT',
            $request->description
        );

        return response()->json(['message' => 'Setoran berhasil dicatat']);
    }
}
