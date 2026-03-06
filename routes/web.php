<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\RekeningKoranController;
use App\Http\Controllers\PendapatanUmumController;
use App\Http\Controllers\PendapatanBpjsController;
use App\Http\Controllers\PendapatanJaminanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\RevenueMasterController;
use App\Http\Controllers\PendapatanLainController;
use App\Http\Controllers\PendapatanKerjasamaController;
use App\Http\Controllers\KodeRekeningController;
use App\Http\Controllers\AnggaranRekeningController;
use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\MouController;
use App\Http\Controllers\PiutangController;
use App\Http\Controllers\PenyesuaianPendapatanController;
use App\Http\Controllers\ExpenditureController;
use App\Http\Controllers\SpjController;
use App\Http\Controllers\DisbursementController;
use App\Http\Controllers\TreasurerCashController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\PenandaTanganController;
use App\Http\Controllers\SiklusUpController;
use App\Http\Controllers\BankAccountLedgerController;
use App\Http\Controllers\RevenueSyncController;

Route::get('/health', function () {
    return response('OK', 200);
});

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth');

Route::get('/logout', function () {
    return redirect()->route('login')->with('info', 'Silakan gunakan tombol keluar pada menu untuk logout.');
});

/*
|--------------------------------------------------------------------------
| DASHBOARD (BASE & AJAX)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', fn() => view('layouts.dashboard'))
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD CONTENT (AJAX VIEW)
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard')->group(function () {
        Route::get('/content/{page}/{param?}', [DashboardController::class, 'content']);
    });

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD SUMMARY & CHART
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard')->group(function () {
        Route::get('/summary', [DashboardController::class, 'summary']);
        Route::get('/chart-7-days', [DashboardController::class, 'chart7Days']);
        Route::get('/chart-rooms', [DashboardController::class, 'chartRooms']);
        Route::get('/chart-expenditure', [DashboardController::class, 'chartExpenditure']);
    });
});

/*
|--------------------------------------------------------------------------
| USERS (ADMIN ONLY)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:ADMIN'])
    ->prefix('dashboard/users')
    ->group(function () {

        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::put('/{user}', [UserController::class, 'update']);
        Route::delete('/{user}', [UserController::class, 'destroy']);
    });

/*
|--------------------------------------------------------------------------
| RUANGAN
|--------------------------------------------------------------------------
*/
Route::middleware('auth')
    ->prefix('dashboard')
    ->group(function () {

        // JSON list (table + dropdown)
        Route::get('/ruangan-list', [RuanganController::class, 'list']);

        Route::middleware('role:ADMIN,USER')
            ->prefix('ruangans')
            ->group(function () {

                Route::get('/', [RuanganController::class, 'index']);
                Route::post('/', [RuanganController::class, 'store']);
                Route::put('/{ruangan}', [RuanganController::class, 'update']);
                Route::delete('/{ruangan}', [RuanganController::class, 'destroy']);
                Route::get('/next-kode', [RuanganController::class, 'nextKode']);
            });
    });

/*
|--------------------------------------------------------------------------
| PERUSAHAAN (JAMINAN)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')
    ->prefix('dashboard')
    ->group(function () {

        // JSON list (table + dropdown)
        Route::get('/perusahaan-list', [PerusahaanController::class, 'list']);

        Route::middleware('role:ADMIN,USER')
            ->prefix('perusahaans')
            ->group(function () {

                Route::get('/', [PerusahaanController::class, 'index']);
                Route::post('/', [PerusahaanController::class, 'store']);
                Route::put('/{perusahaan}', [PerusahaanController::class, 'update']);
                Route::delete('/{perusahaan}', [PerusahaanController::class, 'destroy']);
                Route::get('/next-kode', [PerusahaanController::class, 'nextKode']);
            });
    });

/*
|--------------------------------------------------------------------------
| MOU (KERJASAMA)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')
    ->prefix('dashboard')
    ->group(function () {

        // JSON list (table + dropdown)
        Route::get('/mou-list', [MouController::class, 'list']);

        Route::middleware('role:ADMIN,USER')
            ->prefix('mous')
            ->group(function () {

                Route::get('/', [MouController::class, 'index']);
                Route::post('/', [MouController::class, 'store']);
                Route::put('/{mou}', [MouController::class, 'update']);
                Route::delete('/{mou}', [MouController::class, 'destroy']);
                Route::get('/next-kode', [MouController::class, 'nextKode']);
            });
    });

/*
|--------------------------------------------------------------------------
| PENANDA TANGAN
|--------------------------------------------------------------------------
*/
Route::middleware('auth')
    ->prefix('dashboard')
    ->group(function () {

        Route::get('/penanda-tangan-list', [PenandaTanganController::class, 'list']);

        Route::middleware('role:ADMIN,USER')
            ->prefix('penanda-tangans')
            ->group(function () {
                Route::get('/', [PenandaTanganController::class, 'index']);
                Route::post('/', [PenandaTanganController::class, 'store']);
                Route::put('/{penandaTangan}', [PenandaTanganController::class, 'update']);
                Route::delete('/{penandaTangan}', [PenandaTanganController::class, 'destroy']);
            });
    });

/*
|--------------------------------------------------------------------------
| REKENING KORAN (ADMIN ONLY)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:ADMIN,USER'])
    ->prefix('dashboard/rekening-korans')
    ->group(function () {

        Route::get('/template', [RekeningKoranController::class, 'downloadTemplate']);
        Route::post('/import', [RekeningKoranController::class, 'import']);
        Route::delete('/bulk-delete', [RekeningKoranController::class, 'bulkDelete']);
        Route::get('/print', [RekeningKoranController::class, 'print']);
        Route::post('/saldo-awal', [RekeningKoranController::class, 'setSaldoAwal']);
        Route::delete('/saldo-awal', [RekeningKoranController::class, 'deleteSaldoAwal']);
        Route::get('/', [RekeningKoranController::class, 'index']);
        Route::post('/', [RekeningKoranController::class, 'store']);

        Route::get('/{rekeningKoran}', [RekeningKoranController::class, 'show']);
        Route::put('/{rekeningKoran}', [RekeningKoranController::class, 'update']);
        Route::delete('/{rekeningKoran}', [RekeningKoranController::class, 'destroy']);
    });

Route::middleware(['auth', 'role:ADMIN,USER'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | REVENUE MASTER
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard/revenue-master')->group(function () {
        Route::get('/', [RevenueMasterController::class, 'index']);
        Route::post('/sync', [RevenueSyncController::class, 'syncOrphans'])->middleware('permission:REVENUE_SYNC');
        Route::post('/', [RevenueMasterController::class, 'store']);
        Route::get('/{id}', [RevenueMasterController::class, 'show']);
        Route::put('/{id}', [RevenueMasterController::class, 'update']);
        Route::delete('/{id}', [RevenueMasterController::class, 'destroy']);
        Route::post('/bulk-post', [RevenueMasterController::class, 'bulkPost']);
        Route::post('/bulk-unpost', [RevenueMasterController::class, 'bulkUnpost']);
        Route::post('/{id}/toggle-post', [RevenueMasterController::class, 'togglePost']);
    });

    /*
    |--------------------------------------------------------------------------
    | PENDAPATAN UMUM
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard/pendapatan/umum')->group(function () {
        Route::get('/template', [PendapatanUmumController::class, 'downloadTemplate']);
        Route::post('/import', [PendapatanUmumController::class, 'import']);
        Route::delete('/bulk-delete', [PendapatanUmumController::class, 'bulkDelete']);
        Route::get('/', [PendapatanUmumController::class, 'index']);
        Route::post('/', [PendapatanUmumController::class, 'store']);
        Route::get('/{id}', [PendapatanUmumController::class, 'show']);
        Route::put('/{id}', [PendapatanUmumController::class, 'update']);
        Route::delete('/{id}', [PendapatanUmumController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | PENDAPATAN BPJS
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard/pendapatan/bpjs')->group(function () {
        Route::get('/template', [PendapatanBpjsController::class, 'downloadTemplate']);
        Route::post('/import', [PendapatanBpjsController::class, 'import']);
        Route::delete('/bulk-delete', [PendapatanBpjsController::class, 'bulkDelete']);
        Route::get('/', [PendapatanBpjsController::class, 'index']);
        Route::post('/', [PendapatanBpjsController::class, 'store']);
        Route::get('/{id}', [PendapatanBpjsController::class, 'show']);
        Route::put('/{id}', [PendapatanBpjsController::class, 'update']);
        Route::delete('/{id}', [PendapatanBpjsController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | PENDAPATAN JAMINAN
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard/pendapatan/jaminan')->group(function () {
        Route::get('/template', [PendapatanJaminanController::class, 'downloadTemplate']);
        Route::post('/import', [PendapatanJaminanController::class, 'import']);
        Route::delete('/bulk-delete', [PendapatanJaminanController::class, 'bulkDelete']);
        Route::get('/', [PendapatanJaminanController::class, 'index']);
        Route::post('/', [PendapatanJaminanController::class, 'store']);
        Route::get('/{id}', [PendapatanJaminanController::class, 'show']);
        Route::put('/{id}', [PendapatanJaminanController::class, 'update']);
        Route::delete('/{id}', [PendapatanJaminanController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | PENDAPATAN LAIN-LAIN
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard/pendapatan/lain')->group(function () {
        Route::get('/template', [PendapatanLainController::class, 'downloadTemplate']);
        Route::post('/import', [PendapatanLainController::class, 'import']);
        Route::delete('/bulk-delete', [PendapatanLainController::class, 'bulkDelete']);
        Route::get('/', [PendapatanLainController::class, 'index']);
        Route::post('/', [PendapatanLainController::class, 'store']);
        Route::get('/{id}', [PendapatanLainController::class, 'show']);
        Route::put('/{id}', [PendapatanLainController::class, 'update']);
        Route::delete('/{id}', [PendapatanLainController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | PENDAPATAN KERJASAMA
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard/pendapatan/kerjasama')->group(function () {
        Route::get('/template', [PendapatanKerjasamaController::class, 'downloadTemplate']);
        Route::post('/import', [PendapatanKerjasamaController::class, 'import']);
        Route::delete('/bulk-delete', [PendapatanKerjasamaController::class, 'bulkDelete']);
        Route::get('/', [PendapatanKerjasamaController::class, 'index']);
        Route::post('/', [PendapatanKerjasamaController::class, 'store']);
        Route::get('/{id}', [PendapatanKerjasamaController::class, 'show']);
        Route::put('/{id}', [PendapatanKerjasamaController::class, 'update']);
        Route::delete('/{id}', [PendapatanKerjasamaController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | PIUTANG
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard/piutang')->group(function () {
        Route::get('/', [PiutangController::class, 'index']);
        Route::post('/', [PiutangController::class, 'store']);
        Route::get('/{id}', [PiutangController::class, 'show']);
        Route::put('/{id}', [PiutangController::class, 'update']);
        Route::delete('/{id}', [PiutangController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | PENYESUAIAN PENDAPATAN (POTONGAN & ADM BANK)
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard/penyesuaian')->group(function () {
        Route::get('/', [PenyesuaianPendapatanController::class, 'index']);
        Route::post('/', [PenyesuaianPendapatanController::class, 'store']);
        Route::get('/{id}', [PenyesuaianPendapatanController::class, 'show']);
        Route::put('/{id}', [PenyesuaianPendapatanController::class, 'update']);
        Route::delete('/{id}', [PenyesuaianPendapatanController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | AUDIT-SAFE EXPENDITURE (REDESIGN)
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard/expenditures')->group(function () {
        Route::get('/', [ExpenditureController::class, 'index']);
        Route::post('/', [ExpenditureController::class, 'store']);
        Route::get('/unlinked', [SpjController::class, 'getUnlinkedExpenditures']);
        Route::get('/check-no-bukti', [ExpenditureController::class, 'checkNoBukti']);
        Route::get('/{id}', [ExpenditureController::class, 'show']);
        Route::put('/{id}', [ExpenditureController::class, 'update']);
        Route::delete('/{id}', [ExpenditureController::class, 'destroy']);
    });

    Route::prefix('dashboard/spj')->group(function () {
        Route::get('/', [SpjController::class, 'index']);
        Route::post('/', [SpjController::class, 'store']);
        Route::get('/{id}', [SpjController::class, 'show']);
        Route::get('/{id}/print', [SpjController::class, 'print']);
        Route::put('/{id}/status', [SpjController::class, 'updateStatus']);
        Route::delete('/{id}', [SpjController::class, 'destroy']);
    });

    Route::prefix('dashboard/disbursements')->group(function () {
        Route::get('/', [DisbursementController::class, 'index']);
        Route::post('/', [DisbursementController::class, 'store']);
        Route::get('/next-siklus', [DisbursementController::class, 'getNextSiklus']);
        Route::get('/available-siklus', [DisbursementController::class, 'availableSiklus']);
        Route::get('/sisa-anggaran', [DisbursementController::class, 'getSisaAnggaran']);
        Route::get('/saldo-kas', [DisbursementController::class, 'getSaldoKas']);
        Route::get('/saldo-summary', [DisbursementController::class, 'getSaldoSummary']);
        Route::get('/{id}', [DisbursementController::class, 'show']);
        Route::put('/{id}', [DisbursementController::class, 'update']);
        Route::put('/{id}/status', [DisbursementController::class, 'updateStatus']);
        Route::put('/{id}/revert', [DisbursementController::class, 'revertStatus']);
        Route::delete('/{id}', [DisbursementController::class, 'destroy']);
    });

    Route::get('dashboard/bank-account-ledger', [BankAccountLedgerController::class, 'index']);
    Route::post('dashboard/bank-account-ledger/deposit', [BankAccountLedgerController::class, 'deposit']);
    Route::get('dashboard/bank-account-ledger/saldo-awal', [BankAccountLedgerController::class, 'getSaldoAwal']);
    Route::post('dashboard/bank-account-ledger/saldo-awal', [BankAccountLedgerController::class, 'setSaldoAwal']);
    Route::delete('dashboard/bank-account-ledger/saldo-awal', [BankAccountLedgerController::class, 'deleteSaldoAwal']);
    Route::post('dashboard/bank-account-ledger/adjustment', [BankAccountLedgerController::class, 'adjustment']);
    Route::put('dashboard/bank-account-ledger/adjustment/{id}', [BankAccountLedgerController::class, 'updateAdjustment']);
    Route::delete('dashboard/bank-account-ledger/adjustment/{id}', [BankAccountLedgerController::class, 'destroyAdjustment']);
    Route::put('dashboard/bank-account-ledger/deposit/{id}', [BankAccountLedgerController::class, 'updateDeposit']);
    Route::delete('dashboard/bank-account-ledger/deposit/{id}', [BankAccountLedgerController::class, 'destroyDeposit']);

    // Blade page mapping
    Route::get('dashboard/pengeluaran/rekening-koran', fn() => view('dashboard.pages.pengeluaran.rekening-koran'));

    Route::get('dashboard/treasurer-cash', [TreasurerCashController::class, 'index']);
    Route::post('dashboard/treasurer-cash/sync', [TreasurerCashController::class, 'sync']);
});


/*
|--------------------------------------------------------------------------
| MASTER DATA — KODE REKENING & ANGGARAN
|--------------------------------------------------------------------------
*/
Route::middleware('auth')
    ->prefix('dashboard')
    ->group(function () {

        /*
        |--------------------------------------------------
        | KODE REKENING (CRUD)
        |--------------------------------------------------
        | ⚠️ Jangan diubah endpoint-nya
        */
        Route::get('/master/kode-rekening', [KodeRekeningController::class, 'index']);
        Route::post('/master/kode-rekening', [KodeRekeningController::class, 'store']);
        Route::put('/master/kode-rekening/{id}', [KodeRekeningController::class, 'update']);
        Route::delete('/master/kode-rekening/{id}', [KodeRekeningController::class, 'destroy']);

        /*
        |--------------------------------------------------
        | TREE + TOTAL ANGGARAN (READ ONLY)
        |--------------------------------------------------
        */
        Route::get(
            '/master/kode-rekening-anggaran/{tahun}',
            [KodeRekeningController::class, 'treeAnggaran']
        );

        /*
        |--------------------------------------------------
        | ANGGARAN (INPUT)
        |--------------------------------------------------
        */
        Route::post('/anggaran', [AnggaranRekeningController::class, 'store']);
        Route::get('/anggaran/rincian/{rekening_id}/{tahun}', [AnggaranRekeningController::class, 'showRincian']);

        Route::middleware('role:ADMIN')->group(function () {
            Route::get('/master/activity-logs', [ActivityLogController::class, 'index']);
            Route::delete('/master/activity-logs/purge', [ActivityLogController::class, 'purge']);
            Route::delete('/master/activity-logs/{id}', [ActivityLogController::class, 'destroy']);
        });

        /*
        |--------------------------------------------------
        | SP3BP (PENGESAHAN)
        |--------------------------------------------------
        */
        Route::prefix('pengesahan/sp3bp')->group(function () {
            Route::get('/', [\App\Http\Controllers\SP3BPController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\SP3BPController::class, 'store']);
            Route::get('/{id}', [\App\Http\Controllers\SP3BPController::class, 'show']);
            Route::post('/{id}/generate', [\App\Http\Controllers\SP3BPController::class, 'generate']);
            Route::post('/{id}/sahkan', [\App\Http\Controllers\SP3BPController::class, 'sahkan']);
            Route::post('/{id}/batal-sah', [\App\Http\Controllers\SP3BPController::class, 'batalSah']);
            Route::get('/{id}/print', [\App\Http\Controllers\SP3BPController::class, 'printPdf']);
            Route::delete('/{id}', [\App\Http\Controllers\SP3BPController::class, 'destroy']);
        });

        // LRKB Routes
        Route::prefix('pengesahan/lrkb')->group(function () {
            Route::get('/', [\App\Http\Controllers\LRKBController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\LRKBController::class, 'store']);
            Route::get('/{id}', [\App\Http\Controllers\LRKBController::class, 'show']);
            Route::post('/{id}/generate', [\App\Http\Controllers\LRKBController::class, 'generate']);
            Route::post('/{id}/validate', [\App\Http\Controllers\LRKBController::class, 'validateLrkb']);
            Route::post('/{id}/unvalidate', [\App\Http\Controllers\LRKBController::class, 'unvalidateLrkb']);
            Route::post('/{id}/catatan', [\App\Http\Controllers\LRKBController::class, 'saveCatatan']);
            Route::get('/{id}/print', [\App\Http\Controllers\LRKBController::class, 'print']);
            Route::delete('/{id}', [\App\Http\Controllers\LRKBController::class, 'destroy']);
        });
    });

/*
|--------------------------------------------------------------------------
| VIEW — MODE ANGGARAN (READ ONLY)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard/laporan/data', [LaporanController::class, 'index'])->middleware('auth');
Route::get('/dashboard/laporan/export/pendapatan', [LaporanController::class, 'export'])->middleware('auth');
Route::get('/dashboard/laporan/export/pendapatan-pdf', [LaporanController::class, 'exportPdf'])->middleware('auth');

Route::get('/dashboard/laporan/export/rekon', [LaporanController::class, 'exportRekon'])->middleware('auth');
Route::get('/dashboard/laporan/export/rekon-pdf', [LaporanController::class, 'exportRekonPdf'])->middleware('auth');

Route::get('/dashboard/laporan/export/piutang', [LaporanController::class, 'exportPiutang'])->middleware('auth');
Route::get('/dashboard/laporan/export/piutang-pdf', [LaporanController::class, 'exportPiutangPdf'])->middleware('auth');

Route::get('/dashboard/laporan/export/mou', [LaporanController::class, 'exportMou'])->middleware('auth');
Route::get('/dashboard/laporan/export/mou-pdf', [LaporanController::class, 'exportMouPdf'])->middleware('auth');

Route::get('/dashboard/laporan/export/anggaran', [LaporanController::class, 'exportAnggaran'])->middleware('auth');
Route::get('/dashboard/laporan/export/anggaran-pdf', [LaporanController::class, 'exportAnggaranPdf'])->middleware('auth');
Route::get('/dashboard/laporan/rekon', [LaporanController::class, 'getRekon'])->middleware('auth');
Route::get('/dashboard/laporan/piutang', [LaporanController::class, 'getPiutang'])->middleware('auth');
Route::get('/dashboard/laporan/mou', [LaporanController::class, 'getMou'])->middleware('auth');
Route::get('/dashboard/laporan/anggaran', [LaporanController::class, 'getAnggaran'])->middleware('auth');
Route::get('/dashboard/laporan/pengeluaran', [LaporanController::class, 'getPengeluaran'])->middleware('auth');
Route::get('/dashboard/laporan/dpa', [LaporanController::class, 'getDpa'])->middleware('auth');
Route::get('/dashboard/laporan/export/pengeluaran', [LaporanController::class, 'exportPengeluaran'])->middleware('auth');
Route::get('/dashboard/laporan/export/pengeluaran-pdf', [LaporanController::class, 'exportPengeluaranPdf'])->middleware('auth');

Route::get('/dashboard/laporan/export/dpa', [LaporanController::class, 'exportDpa'])->middleware('auth');
Route::get('/dashboard/laporan/export/dpa-pdf', [LaporanController::class, 'exportDpaPdf'])->middleware('auth');

Route::get('/dashboard/laporan/bku', [LaporanController::class, 'getBku'])->middleware('auth');
Route::get('/dashboard/laporan/export/bku', [LaporanController::class, 'exportBku'])->middleware('auth');
Route::get('/dashboard/laporan/export/bku-pdf', [LaporanController::class, 'exportBkuPdf'])->middleware('auth');

Route::get(
    '/dashboard/pendapatan/anggaran',
    fn() => view('dashboard.pages.pendapatan.anggaran')
)->middleware('auth');
