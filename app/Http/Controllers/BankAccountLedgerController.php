<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankAccountLedger;
use App\Services\BankLedgerService;
use Carbon\Carbon;

class BankAccountLedgerController extends Controller
{
    protected $service;
    protected $cashLedgerService;

    public function __construct(BankLedgerService $service, \App\Services\CashLedgerService $cashLedgerService)
    {
        $this->service = $service;
        $this->cashLedgerService = $cashLedgerService;
    }

    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_RK_VIEW') || auth()->user()->isAdmin(), 403);

        $month = $request->get('month', date('n'));
        $year = $request->get('year', date('Y'));

        $query = BankAccountLedger::whereYear('date', $year);
        if ($month) {
            $query->whereMonth('date', $month);
        }
        if ($request->filled('bank') && $request->bank !== 'Semua Bank') {
            $query->where('bank', $request->bank);
        }

        $data = $query->orderBy('date', 'asc')->orderBy('id', 'asc')->get();

        $bank = $request->get('bank');
        $saldoAwal = 0;

        if ($month == 1) {
            // JANUARI: Ambil dari entri SALDO_AWAL tahun ini
            if ($bank && $bank !== 'Semua Bank') {
                $entry = BankAccountLedger::where('type', 'SALDO_AWAL')
                    ->whereYear('date', $year)
                    ->where('bank', $bank)
                    ->first();
                $saldoAwal = $entry ? (float) $entry->debit : 0;
            } else {
                $saldoAwal = BankAccountLedger::where('type', 'SALDO_AWAL')
                    ->whereYear('date', $year)
                    ->sum('debit');
            }
        } else {
            // FEBRUARI - DESEMBER: Ambil dari SALDO AKHIR bulan sebelumnya
            // Cari entri terakhir tepat sebelum bulan ini di tahun yang sama atau tahun sebelumnya
            $prevDate = Carbon::create($year, $month, 1)->subMonth();

            $querySaldo = BankAccountLedger::where('date', '<=', $prevDate->endOfMonth()->toDateString())
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc');

            if ($bank && $bank !== 'Semua Bank') {
                $entry = $querySaldo->where('bank', $bank)->first();
                $saldoAwal = $entry ? (float) $entry->balance : 0;
            } else {
                // Untuk "Semua Bank", jumlahkan saldo terakhir dari setiap bank
                $banks = BankAccountLedger::distinct()->pluck('bank');
                foreach ($banks as $b) {
                    $lastEntry = BankAccountLedger::where('bank', $b)
                        ->where('date', '<=', $prevDate->endOfMonth()->toDateString())
                        ->orderBy('date', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    $saldoAwal += $lastEntry ? (float) $lastEntry->balance : 0;
                }
            }
        }

        if ($bank && $bank !== 'Semua Bank') {
            $currentBalance = $this->service->getCurrentBalance($bank);
        } else {
            $currentBalance = $this->service->getTotalBalance();
        }

        return response()->json([
            'data' => $data,
            'current_balance' => $currentBalance,
            'saldo_awal' => $saldoAwal
        ]);
    }

    public function deposit(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_RK_CREATE') || auth()->user()->isAdmin(), 403);

        $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'bank' => 'required|string'
        ]);

        $bankEntry = $this->service->recordEntry(
            $request->date,
            'DEPOSIT_MANUAL',
            $request->amount,
            'manual', // arbitrary table name for bank ledger's own grouping if needed
            time(),   // arbitrary ref id for bank ledger's own grouping
            'DEBIT',
            $request->description,
            null,
            $request->bank
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

    public function setSaldoAwal(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_RK_CREATE') || auth()->user()->isAdmin(), 403);

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'bank' => 'required|string'
        ]);

        $tahun = date('Y'); // Or get from session if applicable in this context

        // Record or Update SALDO_AWAL
        // Use a constant ref_id (e.g., '1') instead of time() to ensure updates instead of new insertions
        $this->service->recordEntry(
            $tahun . '-01-01',
            'SALDO_AWAL',
            $request->amount,
            'manual',
            '1',
            'DEBIT',
            'Saldo Awal Tahun ' . $tahun . ' (' . $request->bank . ')',
            null,
            $request->bank
        );

        // Sync to BKU as SISA_KAS
        $this->cashLedgerService->recordEntry(
            $tahun . '-01-01',
            'SISA_KAS',
            $request->amount,
            'bank_account_ledgers',
            0,
            'DEBIT',
            'Saldo Awal Tahun ' . $tahun
        );

        return response()->json(['message' => 'Saldo awal berhasil diset']);
    }

    public function getSaldoAwal(Request $request)
    {
        $bank = $request->query('bank');
        $tahun = date('Y');
        $entry = BankAccountLedger::where('type', 'SALDO_AWAL')
            ->whereYear('date', $tahun)
            ->where('bank', $bank)
            ->first();

        return response()->json(['amount' => $entry ? $entry->debit : 0]);
    }

    public function deleteSaldoAwal(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_RK_DELETE') || auth()->user()->isAdmin(), 403);
        $request->validate([
            'bank' => 'required|string'
        ]);

        // Cari ksemua entry saldo awal berdasarkan bank untuk membersihkan duplikasi jika ada
        $entries = BankAccountLedger::where('type', 'SALDO_AWAL')
            ->where('bank', $request->bank)
            ->get();

        foreach ($entries as $entry) {
            $this->service->removeEntry($entry->ref_table, $entry->ref_id, 'SALDO_AWAL');
            $this->cashLedgerService->removeEntry('bank_account_ledgers', $entry->id, 'SISA_KAS');
        }

        return response()->json(['message' => 'Saldo awal berhasil dihapus']);
    }

    public function updateDeposit(Request $request, $id)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_RK_EDIT') || auth()->user()->isAdmin(), 403);

        $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'bank' => 'required|string'
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
            $request->description,
            null,
            $request->bank
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
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_RK_DELETE') || auth()->user()->isAdmin(), 403);

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

    public function adjustment(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_RK_CREATE') || auth()->user()->isAdmin(), 403);

        $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'direction' => 'required|in:DEBIT,CREDIT',
            'description' => 'required|string|max:255',
            'bank' => 'required|string'
        ]);

        $bankEntry = $this->service->recordEntry(
            $request->date,
            'PENYESUAIAN',
            $request->amount,
            'manual_adj',
            time(),
            $request->direction,
            $request->description,
            null,
            $request->bank
        );

        // Sync to BKU: Double entry (SP2D + REALISASI) to keep balance but record transaction
        $this->cashLedgerService->recordEntry(
            $request->date,
            'PENYESUAIAN_SP2D',
            $request->amount,
            'bank_account_ledgers',
            $bankEntry->id,
            'DEBIT',
            '[Penyesuaian SP2D] ' . $request->description
        );

        $this->cashLedgerService->recordEntry(
            $request->date,
            'PENYESUAIAN_REALISASI',
            $request->amount,
            'bank_account_ledgers',
            $bankEntry->id,
            'CREDIT',
            '[Penyesuaian Realisasi] ' . $request->description
        );

        return response()->json(['message' => 'Penyesuaian berhasil dicatat']);
    }

    public function updateAdjustment(Request $request, $id)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_RK_EDIT') || auth()->user()->isAdmin(), 403);

        $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'direction' => 'required|in:DEBIT,CREDIT',
            'description' => 'required|string|max:255',
            'bank' => 'required|string'
        ]);

        $entry = BankAccountLedger::findOrFail($id);
        if ($entry->type !== 'PENYESUAIAN') {
            return response()->json(['message' => 'Hanya mutasi Penyesuaian yang dapat diubah'], 422);
        }

        $bankEntry = $this->service->recordEntry(
            $request->date,
            'PENYESUAIAN',
            $request->amount,
            $entry->ref_table,
            $entry->ref_id,
            $request->direction,
            $request->description,
            null,
            $request->bank
        );

        // Sync to BKU
        $this->cashLedgerService->recordEntry(
            $request->date,
            'PENYESUAIAN_SP2D',
            $request->amount,
            'bank_account_ledgers',
            $bankEntry->id,
            'DEBIT',
            '[Penyesuaian SP2D] ' . $request->description
        );

        $this->cashLedgerService->recordEntry(
            $request->date,
            'PENYESUAIAN_REALISASI',
            $request->amount,
            'bank_account_ledgers',
            $bankEntry->id,
            'CREDIT',
            '[Penyesuaian Realisasi] ' . $request->description
        );

        return response()->json(['message' => 'Penyesuaian berhasil diperbarui']);
    }

    public function destroyAdjustment($id)
    {
        abort_unless(auth()->user()->hasPermission('PENGELUARAN_RK_DELETE') || auth()->user()->isAdmin(), 403);

        $entry = BankAccountLedger::findOrFail($id);
        if ($entry->type !== 'PENYESUAIAN') {
            return response()->json(['message' => 'Hanya mutasi Penyesuaian yang dapat dihapus'], 422);
        }

        $this->service->removeEntry($entry->ref_table, $entry->ref_id, 'PENYESUAIAN');

        // Remove from BKU
        $this->cashLedgerService->removeEntry('bank_account_ledgers', $entry->id, 'PENYESUAIAN_SP2D');
        $this->cashLedgerService->removeEntry('bank_account_ledgers', $entry->id, 'PENYESUAIAN_REALISASI');

        return response()->json(['message' => 'Mutasi penyesuaian berhasil dihapus']);
    }
}
