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

        $bankEntry = $this->service->recordEntry(
            $request->date,
            'DEPOSIT_MANUAL',
            $request->amount,
            'manual', // arbitrary table name for bank ledger's own grouping if needed
            time(),   // arbitrary ref id for bank ledger's own grouping
            'DEBIT',
            $request->description
        );

        // Also record in BKU (Treasurer Cash) as Transfer Penerimaan
        // Use the actual bank_account_ledgers id as ref_id to ensure report mapping works
        app(\App\Services\CashLedgerService::class)->recordEntry(
            $request->date,
            'DEPOSIT_MANUAL',
            $request->amount,
            'bank_account_ledgers',
            $bankEntry->id,
            'DEBIT',
            $request->description
        );

        return response()->json(['message' => 'Setoran berhasil dicatat']);
    }

    public function updateDeposit(Request $request, $id)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_CAIR') || auth()->user()->isAdmin(), 403);

        $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255'
        ]);

        $entry = BankAccountLedger::findOrFail($id);
        if ($entry->type !== 'DEPOSIT_MANUAL') {
            return response()->json(['message' => 'Hanya mutasi Deposit Manual yang dapat diubah'], 422);
        }

        // Update Bank Ledger
        $bankEntry = $this->service->recordEntry(
            $request->date,
            'DEPOSIT_MANUAL',
            $request->amount,
            $entry->ref_table,
            $entry->ref_id,
            'DEBIT',
            $request->description
        );

        // Update Cash Ledger
        app(\App\Services\CashLedgerService::class)->recordEntry(
            $request->date,
            'DEPOSIT_MANUAL',
            $request->amount,
            'bank_account_ledgers',
            $bankEntry->id,
            'DEBIT',
            $request->description
        );

        return response()->json(['message' => 'Setoran berhasil diperbarui']);
    }

    public function destroyDeposit($id)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_CAIR') || auth()->user()->isAdmin(), 403);

        $entry = BankAccountLedger::findOrFail($id);
        if ($entry->type !== 'DEPOSIT_MANUAL') {
            return response()->json(['message' => 'Hanya mutasi Deposit Manual yang dapat dihapus'], 422);
        }

        // Remove from BKU first using this entry's ID
        app(\App\Services\CashLedgerService::class)->removeEntry('bank_account_ledgers', $entry->id, 'DEPOSIT_MANUAL');

        // Then remove from bank ledger
        $this->service->removeEntry($entry->ref_table, $entry->ref_id, 'DEPOSIT_MANUAL');

        return response()->json(['message' => 'Mutasi deposit berhasil dihapus']);
    }
}
