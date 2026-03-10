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
use App\Http\Controllers\BkuPenerimaanController;
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
    Route::prefix('dashboard')->middleware('permission:DASHBOARD_VIEW')->group(function () {
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
Route::middleware(['auth', 'permission:USER_VIEW'])
    ->prefix('dashboard/users')
    ->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store'])->middleware('permission:USER_MANAGE');
        Route::get('/{user}', [UserController::class, 'show']);
        Route::put('/{user}', [UserController::class, 'update'])->middleware('permission:USER_MANAGE');
        Route::delete('/{user}', [UserController::class, 'destroy'])->middleware('permission:USER_MANAGE');
        Route::post('/{user}/permissions', [UserController::class, 'updatePermissions'])->middleware('permission:USER_PERM');
    });

Route::middleware(['auth', 'permission:LOG_VIEW'])
    ->prefix('dashboard/logs')
    ->group(function () {
        Route::get('/', [ActivityLogController::class, 'index']);
        Route::delete('/purge', [ActivityLogController::class, 'purge'])->middleware('permission:LOG_MANAGE');
    });

/*
|--------------------------------------------------------------------------
| RUANGAN
|--------------------------------------------------------------------------
*/
Route::middleware('auth')
    ->prefix('dashboard')
    ->group(function () {
        Route::get('/ruangan-list', [RuanganController::class, 'list']);
        Route::middleware('permission:RUANGAN_VIEW')
            ->prefix('ruangans')
            ->group(function () {
                Route::get('/', [RuanganController::class, 'index']);
                Route::post('/', [RuanganController::class, 'store'])->middleware('permission:RUANGAN_MANAGE');
                Route::put('/{ruangan}', [RuanganController::class, 'update'])->middleware('permission:RUANGAN_MANAGE');
                Route::delete('/{ruangan}', [RuanganController::class, 'destroy'])->middleware('permission:RUANGAN_MANAGE');
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
        Route::get('/perusahaan-list', [PerusahaanController::class, 'list']);
        Route::middleware('permission:PERUSAHAAN_VIEW')
            ->prefix('perusahaans')
            ->group(function () {
                Route::get('/', [PerusahaanController::class, 'index']);
                Route::post('/', [PerusahaanController::class, 'store'])->middleware('permission:PERUSAHAAN_MANAGE');
                Route::put('/{perusahaan}', [PerusahaanController::class, 'update'])->middleware('permission:PERUSAHAAN_MANAGE');
                Route::delete('/{perusahaan}', [PerusahaanController::class, 'destroy'])->middleware('permission:PERUSAHAAN_MANAGE');
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
        Route::get('/mou-list', [MouController::class, 'list']);
        Route::middleware('permission:MOU_VIEW')
            ->prefix('mous')
            ->group(function () {
                Route::get('/', [MouController::class, 'index']);
                Route::post('/', [MouController::class, 'store'])->middleware('permission:MOU_MANAGE');
                Route::put('/{mou}', [MouController::class, 'update'])->middleware('permission:MOU_MANAGE');
                Route::delete('/{mou}', [MouController::class, 'destroy'])->middleware('permission:MOU_MANAGE');
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
        Route::middleware('permission:PENANDATANGAN_VIEW')
            ->prefix('penanda-tangans')
            ->group(function () {
                Route::get('/', [PenandaTanganController::class, 'index']);
                Route::post('/', [PenandaTanganController::class, 'store'])->middleware('permission:PENANDATANGAN_MANAGE');
                Route::put('/{penandaTangan}', [PenandaTanganController::class, 'update'])->middleware('permission:PENANDATANGAN_MANAGE');
                Route::delete('/{penandaTangan}', [PenandaTanganController::class, 'destroy'])->middleware('permission:PENANDATANGAN_MANAGE');
            });
    });

/*
|--------------------------------------------------------------------------
| REKENING KORAN (ADMIN ONLY)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'permission:REKKOR_VIEW'])
    ->prefix('dashboard/rekening-korans')
    ->group(function () {

        Route::get('/template', [RekeningKoranController::class, 'downloadTemplate']);
        Route::post('/import', [RekeningKoranController::class, 'import'])->middleware('permission:REKKOR_MANAGE');
        Route::delete('/bulk-delete', [RekeningKoranController::class, 'bulkDelete'])->middleware('permission:REKKOR_MANAGE');
        Route::get('/print', [RekeningKoranController::class, 'print']);
        Route::post('/saldo-awal', [RekeningKoranController::class, 'setSaldoAwal'])->middleware('permission:REKKOR_MANAGE');
        Route::delete('/saldo-awal', [RekeningKoranController::class, 'deleteSaldoAwal'])->middleware('permission:REKKOR_MANAGE');
        Route::get('/', [RekeningKoranController::class, 'index']);
        Route::post('/', [RekeningKoranController::class, 'store'])->middleware('permission:REKKOR_MANAGE');

        Route::get('/{rekeningKoran}', [RekeningKoranController::class, 'show']);
        Route::put('/{rekeningKoran}', [RekeningKoranController::class, 'update'])->middleware('permission:REKKOR_MANAGE');
        Route::delete('/{rekeningKoran}', [RekeningKoranController::class, 'destroy'])->middleware('permission:REKKOR_MANAGE');
    });

Route::middleware(['auth', 'role:ADMIN,USER'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | REVENUE MASTER
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard/revenue-master')->group(function () {
        Route::get('/', [RevenueMasterController::class, 'index']);
        Route::post('/sync', [RevenueSyncController::class, 'syncOrphans'])->middleware('permission:REVENUE_MASTER_SYNC');
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
        Route::post('/import', [PendapatanUmumController::class, 'import'])->middleware('permission:PENDAPATAN_UMUM_MANAGE');
        Route::delete('/bulk-delete', [PendapatanUmumController::class, 'bulkDelete'])->middleware('permission:PENDAPATAN_UMUM_MANAGE');
        Route::get('/', [PendapatanUmumController::class, 'index'])->middleware('permission:PENDAPATAN_UMUM_VIEW');
        Route::post('/', [PendapatanUmumController::class, 'store'])->middleware('permission:PENDAPATAN_UMUM_MANAGE');
        Route::get('/{id}', [PendapatanUmumController::class, 'show'])->middleware('permission:PENDAPATAN_UMUM_VIEW');
        Route::put('/{id}', [PendapatanUmumController::class, 'update'])->middleware('permission:PENDAPATAN_UMUM_MANAGE');
        Route::delete('/{id}', [PendapatanUmumController::class, 'destroy'])->middleware('permission:PENDAPATAN_UMUM_MANAGE');
    });

    /*
    |--------------------------------------------------------------------------
    | PENDAPATAN BPJS
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard/pendapatan/bpjs')->group(function () {
        Route::get('/template', [PendapatanBpjsController::class, 'downloadTemplate']);
        Route::post('/import', [PendapatanBpjsController::class, 'import'])->middleware('permission:PENDAPATAN_BPJS_MANAGE');
        Route::delete('/bulk-delete', [PendapatanBpjsController::class, 'bulkDelete'])->middleware('permission:PENDAPATAN_BPJS_MANAGE');
        Route::get('/', [PendapatanBpjsController::class, 'index'])->middleware('permission:PENDAPATAN_BPJS_VIEW');
        Route::post('/', [PendapatanBpjsController::class, 'store'])->middleware('permission:PENDAPATAN_BPJS_MANAGE');
        Route::get('/{id}', [PendapatanBpjsController::class, 'show'])->middleware('permission:PENDAPATAN_BPJS_VIEW');
        Route::put('/{id}', [PendapatanBpjsController::class, 'update'])->middleware('permission:PENDAPATAN_BPJS_MANAGE');
        Route::delete('/{id}', [PendapatanBpjsController::class, 'destroy'])->middleware('permission:PENDAPATAN_BPJS_MANAGE');
    });

    /*
    |--------------------------------------------------------------------------
    | PENDAPATAN JAMINAN
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard/pendapatan/jaminan')->group(function () {
        Route::get('/template', [PendapatanJaminanController::class, 'downloadTemplate']);
        Route::post('/import', [PendapatanJaminanController::class, 'import'])->middleware('permission:PENDAPATAN_JAMINAN_MANAGE');
        Route::delete('/bulk-delete', [PendapatanJaminanController::class, 'bulkDelete'])->middleware('permission:PENDAPATAN_JAMINAN_MANAGE');
        Route::get('/', [PendapatanJaminanController::class, 'index'])->middleware('permission:PENDAPATAN_JAMINAN_VIEW');
        Route::post('/', [PendapatanJaminanController::class, 'store'])->middleware('permission:PENDAPATAN_JAMINAN_MANAGE');
        Route::get('/{id}', [PendapatanJaminanController::class, 'show'])->middleware('permission:PENDAPATAN_JAMINAN_VIEW');
        Route::put('/{id}', [PendapatanJaminanController::class, 'update'])->middleware('permission:PENDAPATAN_JAMINAN_MANAGE');
        Route::delete('/{id}', [PendapatanJaminanController::class, 'destroy'])->middleware('permission:PENDAPATAN_JAMINAN_MANAGE');
    });

    /*
    |--------------------------------------------------------------------------
    | PENDAPATAN LAIN-LAIN
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard/pendapatan/lain')->group(function () {
        Route::get('/template', [PendapatanLainController::class, 'downloadTemplate']);
        Route::post('/import', [PendapatanLainController::class, 'import'])->middleware('permission:PENDAPATAN_LAIN_MANAGE');
        Route::delete('/bulk-delete', [PendapatanLainController::class, 'bulkDelete'])->middleware('permission:PENDAPATAN_LAIN_MANAGE');
        Route::get('/', [PendapatanLainController::class, 'index'])->middleware('permission:PENDAPATAN_LAIN_VIEW');
        Route::post('/', [PendapatanLainController::class, 'store'])->middleware('permission:PENDAPATAN_LAIN_MANAGE');
        Route::get('/{id}', [PendapatanLainController::class, 'show'])->middleware('permission:PENDAPATAN_LAIN_VIEW');
        Route::put('/{id}', [PendapatanLainController::class, 'update'])->middleware('permission:PENDAPATAN_LAIN_MANAGE');
        Route::delete('/{id}', [PendapatanLainController::class, 'destroy'])->middleware('permission:PENDAPATAN_LAIN_MANAGE');
    });

    /*
    |--------------------------------------------------------------------------
    | PENDAPATAN KERJASAMA
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard/pendapatan/kerjasama')->group(function () {
        Route::get('/template', [PendapatanKerjasamaController::class, 'downloadTemplate']);
        Route::post('/import', [PendapatanKerjasamaController::class, 'import'])->middleware('permission:PENDAPATAN_KERJA_MANAGE');
        Route::delete('/bulk-delete', [PendapatanKerjasamaController::class, 'bulkDelete'])->middleware('permission:PENDAPATAN_KERJA_MANAGE');
        Route::get('/', [PendapatanKerjasamaController::class, 'index'])->middleware('permission:PENDAPATAN_KERJA_VIEW');
        Route::post('/', [PendapatanKerjasamaController::class, 'store'])->middleware('permission:PENDAPATAN_KERJA_MANAGE');
        Route::get('/{id}', [PendapatanKerjasamaController::class, 'show'])->middleware('permission:PENDAPATAN_KERJA_VIEW');
        Route::put('/{id}', [PendapatanKerjasamaController::class, 'update'])->middleware('permission:PENDAPATAN_KERJA_MANAGE');
        Route::delete('/{id}', [PendapatanKerjasamaController::class, 'destroy'])->middleware('permission:PENDAPATAN_KERJA_MANAGE');
    });

    /*
    |--------------------------------------------------------------------------
    | PIUTANG
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard/piutang')->group(function () {
        Route::get('/', [PiutangController::class, 'index'])->middleware('permission:PIUTANG_VIEW');
        Route::post('/', [PiutangController::class, 'store'])->middleware('permission:PIUTANG_MANAGE');
        Route::get('/{id}', [PiutangController::class, 'show'])->middleware('permission:PIUTANG_VIEW');
        Route::put('/{id}', [PiutangController::class, 'update'])->middleware('permission:PIUTANG_MANAGE');
        Route::delete('/{id}', [PiutangController::class, 'destroy'])->middleware('permission:PIUTANG_MANAGE');
    });

    /*
    |--------------------------------------------------------------------------
    | PENYESUAIAN PENDAPATAN (POTONGAN & ADM BANK)
    |--------------------------------------------------------------------------
    | Mapped to PENDAPATAN_UMUM permissions since they affect cash collections.
    */
    Route::prefix('dashboard/penyesuaian')->group(function () {
        Route::get('/', [PenyesuaianPendapatanController::class, 'index'])->middleware('permission:PENYESUAIAN_VIEW');
        Route::post('/', [PenyesuaianPendapatanController::class, 'store'])->middleware('permission:PIUTANG_MANAGE');
        Route::get('/{id}', [PenyesuaianPendapatanController::class, 'show'])->middleware('permission:PENYESUAIAN_VIEW');
        Route::put('/{id}', [PenyesuaianPendapatanController::class, 'update'])->middleware('permission:PIUTANG_MANAGE');
        Route::delete('/{id}', [PenyesuaianPendapatanController::class, 'destroy'])->middleware('permission:PIUTANG_MANAGE');
    });

    /*
    |--------------------------------------------------------------------------
    | AUDIT-SAFE EXPENDITURE (REDESIGN)
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard/expenditures')->group(function () {
        Route::get('/', [ExpenditureController::class, 'index'])->middleware('permission:BELANJA_VIEW');
        Route::post('/', [ExpenditureController::class, 'store'])->middleware('permission:BELANJA_MANAGE');
        Route::get('/unlinked', [SpjController::class, 'getUnlinkedExpenditures'])->middleware('permission:BELANJA_VIEW');
        Route::get('/check-no-bukti', [ExpenditureController::class, 'checkNoBukti'])->middleware('permission:BELANJA_VIEW');
        Route::get('/{id}', [ExpenditureController::class, 'show'])->middleware('permission:BELANJA_VIEW');
        Route::put('/{id}', [ExpenditureController::class, 'update'])->middleware('permission:BELANJA_MANAGE');
        Route::delete('/{id}', [ExpenditureController::class, 'destroy'])->middleware('permission:BELANJA_MANAGE');
    });

    Route::prefix('dashboard/spj')->group(function () {
        Route::get('/', [SpjController::class, 'index'])->middleware('permission:SPJ_VIEW');
        Route::post('/', [SpjController::class, 'store'])->middleware('permission:SPJ_MANAGE');
        Route::get('/{id}', [SpjController::class, 'show'])->middleware('permission:SPJ_VIEW');
        Route::get('/{id}/print', [SpjController::class, 'print'])->middleware('permission:SPJ_PRINT');
        Route::put('/{id}/status', [SpjController::class, 'updateStatus'])->middleware('permission:SPJ_MANAGE');
        Route::delete('/{id}', [SpjController::class, 'destroy'])->middleware('permission:SPJ_MANAGE');
    });

    Route::prefix('dashboard/disbursements')->group(function () {
        Route::get('/', [DisbursementController::class, 'index'])->middleware('permission:SPP_VIEW,SPM_VIEW,SP2D_VIEW');
        Route::post('/', [DisbursementController::class, 'store'])->middleware('permission:SPP_MANAGE');
        Route::get('/next-siklus', [DisbursementController::class, 'getNextSiklus']);
        Route::get('/available-siklus', [DisbursementController::class, 'availableSiklus']);
        Route::get('/sisa-anggaran', [DisbursementController::class, 'getSisaAnggaran']);
        Route::get('/saldo-kas', [DisbursementController::class, 'getSaldoKas']);
        Route::get('/saldo-summary', [DisbursementController::class, 'getSaldoSummary']);
        Route::get('/{id}', [DisbursementController::class, 'show'])->middleware('permission:SPP_VIEW,SPM_VIEW,SP2D_VIEW');
        Route::put('/{id}', [DisbursementController::class, 'update'])->middleware('permission:SPP_MANAGE,SPM_MANAGE,SP2D_MANAGE');
        Route::put('/{id}/status', [DisbursementController::class, 'updateStatus'])->middleware('permission:SPP_MANAGE,SPM_MANAGE,SP2D_MANAGE');
        Route::put('/{id}/revert', [DisbursementController::class, 'revertStatus'])->middleware('permission:SPP_MANAGE,SPM_MANAGE,SP2D_MANAGE');
        Route::delete('/{id}', [DisbursementController::class, 'destroy'])->middleware('permission:SPP_MANAGE,SPM_MANAGE,SP2D_MANAGE');
    });

    Route::get('dashboard/bank-account-ledger', [BankAccountLedgerController::class, 'index'])->middleware('permission:REK_KORAN_PENG_VIEW');
    Route::post('dashboard/bank-account-ledger/deposit', [BankAccountLedgerController::class, 'deposit'])->middleware('permission:REK_KORAN_PENG_IMPORT');
    Route::get('dashboard/bank-account-ledger/saldo-awal', [BankAccountLedgerController::class, 'getSaldoAwal'])->middleware('permission:REK_KORAN_PENG_VIEW');
    Route::post('dashboard/bank-account-ledger/saldo-awal', [BankAccountLedgerController::class, 'setSaldoAwal'])->middleware('permission:REK_KORAN_PENG_IMPORT');
    Route::delete('dashboard/bank-account-ledger/saldo-awal', [BankAccountLedgerController::class, 'deleteSaldoAwal'])->middleware('permission:REK_KORAN_PENG_MANAGE');
    Route::post('dashboard/bank-account-ledger/adjustment', [BankAccountLedgerController::class, 'adjustment'])->middleware('permission:REK_KORAN_PENG_IMPORT');
    Route::put('dashboard/bank-account-ledger/adjustment/{id}', [BankAccountLedgerController::class, 'updateAdjustment'])->middleware('permission:REK_KORAN_PENG_MANAGE');
    Route::delete('dashboard/bank-account-ledger/adjustment/{id}', [BankAccountLedgerController::class, 'destroyAdjustment'])->middleware('permission:REK_KORAN_PENG_MANAGE');
    Route::put('dashboard/bank-account-ledger/deposit/{id}', [BankAccountLedgerController::class, 'updateDeposit'])->middleware('permission:REK_KORAN_PENG_MANAGE');
    Route::delete('dashboard/bank-account-ledger/deposit/{id}', [BankAccountLedgerController::class, 'destroyDeposit'])->middleware('permission:REK_KORAN_PENG_MANAGE');

    // Blade page mapping
    Route::get('dashboard/pengeluaran/rekening-koran', fn() => view('dashboard.pages.pengeluaran.rekening-koran'))
        ->middleware('permission:REK_KORAN_PENG_VIEW');

    Route::get('dashboard/treasurer-cash', [TreasurerCashController::class, 'index'])->middleware('permission:BKU_PENGELUARAN_VIEW');
    Route::get('dashboard/bku-penerimaan', [BkuPenerimaanController::class, 'index'])->middleware('permission:BKU_PENDAPATAN_VIEW');
    Route::post('dashboard/treasurer-cash/sync', [TreasurerCashController::class, 'sync'])->middleware('permission:BKU_PENGELUARAN_SYNC');
    Route::post('dashboard/bku-penerimaan/sync', [BkuPenerimaanController::class, 'sync'])->middleware('permission:BKU_PENDAPATAN_SYNC');
    Route::get('dashboard/bku-penerimaan/export/pdf', [BkuPenerimaanController::class, 'exportPdf'])->middleware('permission:BKU_PENDAPATAN_EXPORT');
    Route::post('dashboard/bku-penerimaan', [BkuPenerimaanController::class, 'store'])->middleware('permission:BKU_PENDAPATAN_MANAGE');
    Route::delete('dashboard/bku-penerimaan/{id}', [BkuPenerimaanController::class, 'destroy'])->middleware('permission:BKU_PENDAPATAN_MANAGE');
});


/*
|--------------------------------------------------------------------------
| MASTER DATA - KODE REKENING & ANGGARAN
|--------------------------------------------------------------------------
*/
Route::middleware('auth')
    ->prefix('dashboard')
    ->group(function () {

        /*
        |--------------------------------------------------
        | KODE REKENING (CRUD)
        |--------------------------------------------------
        | Ã¢Å¡ Ã¯¸ Jangan diubah endpoint-nya
        */
        Route::get('/master/kode-rekening', [KodeRekeningController::class, 'index'])->middleware('permission:KODE_REKENING_PENDAPATAN_VIEW');
        Route::post('/master/kode-rekening', [KodeRekeningController::class, 'store'])->middleware('permission:KODE_REKENING_PENDAPATAN_MANAGE');
        Route::put('/master/kode-rekening/{id}', [KodeRekeningController::class, 'update'])->middleware('permission:KODE_REKENING_PENDAPATAN_MANAGE');
        Route::delete('/master/kode-rekening/{id}', [KodeRekeningController::class, 'destroy'])->middleware('permission:KODE_REKENING_PENDAPATAN_MANAGE');

        /*
        |--------------------------------------------------
        | TREE + TOTAL ANGGARAN (READ ONLY)
        |--------------------------------------------------
        */
        Route::get(
            '/master/kode-rekening-anggaran/{tahun}',
            [KodeRekeningController::class, 'treeAnggaran']
        )->middleware('permission:ANGGARAN_PENDAPATAN_VIEW,ANGGARAN_PENGELUARAN_VIEW');

        /*
        |--------------------------------------------------
        | ANGGARAN (INPUT)
        |--------------------------------------------------
        */
        Route::post('/anggaran', [AnggaranRekeningController::class, 'store'])->middleware('permission:ANGGARAN_PENDAPATAN_MANAGE');
        Route::get('/anggaran/rincian/{rekening_id}/{tahun}', [AnggaranRekeningController::class, 'showRincian'])->middleware('permission:ANGGARAN_PENDAPATAN_VIEW');

        Route::middleware('permission:LOG_VIEW')->group(function () {
            Route::get('/master/activity-logs', [ActivityLogController::class, 'index']);
            Route::delete('/master/activity-logs/purge', [ActivityLogController::class, 'purge'])->middleware('permission:LOG_MANAGE');
            Route::delete('/master/activity-logs/{id}', [ActivityLogController::class, 'destroy'])->middleware('permission:LOG_MANAGE');
        });

        /*
        |--------------------------------------------------
        | SP3BP (PENGESAHAN)
        |--------------------------------------------------
        */
        Route::prefix('pengesahan/sp3bp')->group(function () {
            Route::get('/', [\App\Http\Controllers\SP3BPController::class, 'index'])->middleware('permission:SP3BP_VIEW');
            Route::get('/{id}', [\App\Http\Controllers\SP3BPController::class, 'show'])->middleware('permission:SP3BP_VIEW');
            Route::post('/{id}/generate', [\App\Http\Controllers\SP3BPController::class, 'generate'])->middleware('permission:SP3BP_GENERATE');
            Route::post('/{id}/sahkan', [\App\Http\Controllers\SP3BPController::class, 'sahkan'])->middleware('permission:SP3BP_APPROVE');
            Route::post('/{id}/batal-sah', [\App\Http\Controllers\SP3BPController::class, 'batalSah'])->middleware('permission:SP3BP_APPROVE');
            Route::get('/{id}/print', [\App\Http\Controllers\SP3BPController::class, 'printPdf'])->middleware('permission:SP3BP_PRINT');
            Route::delete('/{id}', [\App\Http\Controllers\SP3BPController::class, 'destroy'])->middleware('permission:SP3BP_MANAGE');
        });

        // LRKB Routes
        Route::prefix('pengesahan/lrkb')->group(function () {
            Route::get('/', [\App\Http\Controllers\LRKBController::class, 'index'])->middleware('permission:LRKB_VIEW');
            Route::get('/{id}', [\App\Http\Controllers\LRKBController::class, 'show'])->middleware('permission:LRKB_VIEW');
            Route::post('/{id}/generate', [\App\Http\Controllers\LRKBController::class, 'generate'])->middleware('permission:LRKB_GENERATE');
            Route::post('/{id}/validate', [\App\Http\Controllers\LRKBController::class, 'validateLrkb'])->middleware('permission:LRKB_APPROVE');
            Route::post('/{id}/unvalidate', [\App\Http\Controllers\LRKBController::class, 'unvalidateLrkb'])->middleware('permission:LRKB_APPROVE');
            Route::get('/{id}/print', [\App\Http\Controllers\LRKBController::class, 'print'])->middleware('permission:LRKB_PRINT');
            Route::delete('/{id}', [\App\Http\Controllers\LRKBController::class, 'destroy'])->middleware('permission:LRKB_MANAGE');
        });

        // SPTJB Routes
        Route::prefix('pengesahan/sptjb')->group(function () {
            Route::get('/', [\App\Http\Controllers\SPTJBController::class, 'index'])->middleware('permission:SPTJB_VIEW');
            Route::get('/{id}', [\App\Http\Controllers\SPTJBController::class, 'show'])->middleware('permission:SPTJB_VIEW');
            Route::post('/{id}/generate', [\App\Http\Controllers\SPTJBController::class, 'generate'])->middleware('permission:SPTJB_GENERATE');
            Route::post('/{id}/validate', [\App\Http\Controllers\SPTJBController::class, 'validateSptjb'])->middleware('permission:SPTJB_APPROVE');
            Route::get('/{id}/print', [\App\Http\Controllers\SPTJBController::class, 'print'])->middleware('permission:SPTJB_PRINT');
            Route::delete('/{id}', [\App\Http\Controllers\SPTJBController::class, 'destroy'])->middleware('permission:SPTJB_MANAGE');
        });
    });

/*
|--------------------------------------------------------------------------
| VIEW - MODE ANGGARAN (READ ONLY)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/laporan/data', [LaporanController::class, 'index'])->middleware('permission:LAP_PENDAPATAN_VIEW');
    Route::get('/dashboard/laporan/export/pendapatan', [LaporanController::class, 'export'])->middleware('permission:LAP_PENDAPATAN_EXPORT');
    Route::get('/dashboard/laporan/export/pendapatan-pdf', [LaporanController::class, 'exportPdf'])->middleware('permission:LAP_PENDAPATAN_EXPORT');

    Route::get('/dashboard/laporan/export/rekon', [LaporanController::class, 'exportRekon'])->middleware('permission:LAP_REKON_EXPORT');
    Route::get('/dashboard/laporan/export/rekon-pdf', [LaporanController::class, 'exportRekonPdf'])->middleware('permission:LAP_REKON_EXPORT');

    Route::get('/dashboard/laporan/export/piutang', [LaporanController::class, 'exportPiutang'])->middleware('permission:LAP_PIUTANG_EXPORT');
    Route::get('/dashboard/laporan/export/piutang-pdf', [LaporanController::class, 'exportPiutangPdf'])->middleware('permission:LAP_PIUTANG_EXPORT');

    Route::get('/dashboard/laporan/export/mou', [LaporanController::class, 'exportMou'])->middleware('permission:LAP_MOU_EXPORT');
    Route::get('/dashboard/laporan/export/mou-pdf', [LaporanController::class, 'exportMouPdf'])->middleware('permission:LAP_MOU_EXPORT');

    Route::get('/dashboard/laporan/export/anggaran', [LaporanController::class, 'exportAnggaran'])->middleware('permission:LAP_LRA_EXPORT');
    Route::get('/dashboard/laporan/export/anggaran-pdf', [LaporanController::class, 'exportAnggaranPdf'])->middleware('permission:LAP_LRA_EXPORT');
    Route::get('/dashboard/laporan/rekon', [LaporanController::class, 'getRekon'])->middleware('permission:LAP_REKON_VIEW');
    Route::get('/dashboard/laporan/piutang', [LaporanController::class, 'getPiutang'])->middleware('permission:LAP_PIUTANG_VIEW');
    Route::get('/dashboard/laporan/mou', [LaporanController::class, 'getMou'])->middleware('permission:LAP_MOU_VIEW');
    Route::get('/dashboard/laporan/anggaran', [LaporanController::class, 'getAnggaran'])->middleware('permission:LAP_LRA_VIEW');
    Route::get('/dashboard/laporan/lra', [LaporanController::class, 'getAnggaran'])->middleware('permission:LAP_LRA_VIEW');
    Route::get('/dashboard/laporan/pengeluaran', [LaporanController::class, 'pengeluaran'])->middleware('permission:LAP_PENGELUARAN_VIEW');
    Route::get('/dashboard/laporan/dpa', [LaporanController::class, 'getDpa'])->middleware('permission:LAP_DPA_VIEW');
    Route::get('/dashboard/laporan/export/pengeluaran', [LaporanController::class, 'exportPengeluaran'])->middleware('permission:LAP_PENGELUARAN_EXPORT');
    Route::get('/dashboard/laporan/export/pengeluaran-pdf', [LaporanController::class, 'exportPengeluaranPdf'])->middleware('permission:LAP_PENGELUARAN_EXPORT');

    Route::get('/dashboard/laporan/export/dpa', [LaporanController::class, 'exportDpa'])->middleware('permission:LAP_DPA_EXPORT');
    Route::get('/dashboard/laporan/export/dpa-pdf', [LaporanController::class, 'exportDpaPdf'])->middleware('permission:LAP_DPA_EXPORT');

    Route::get('/dashboard/laporan/bku', [LaporanController::class, 'getBku'])->middleware('permission:BKU_PENDAPATAN_VIEW');
    Route::get('/dashboard/laporan/export/bku', [LaporanController::class, 'exportBku'])->middleware('permission:BKU_PENDAPATAN_EXPORT');
    Route::get('/dashboard/laporan/export/bku-pdf', [LaporanController::class, 'exportBkuPdf'])->middleware('permission:BKU_PENDAPATAN_EXPORT');

    Route::get('/dashboard/laporan/lak', [LaporanController::class, 'getLak'])->middleware('permission:LAP_LAK_VIEW');
    Route::get('/dashboard/laporan/export/lak', [LaporanController::class, 'exportLak'])->middleware('permission:LAP_LAK_EXPORT');
    Route::get('/dashboard/laporan/export/lak-pdf', [LaporanController::class, 'exportLakPdf'])->middleware('permission:LAP_LAK_EXPORT');

    Route::get('/dashboard/laporan/neraca', [LaporanController::class, 'getNeraca'])->middleware('permission:LAP_NERACA_VIEW');
    Route::get('/dashboard/laporan/neraca/manual', [LaporanController::class, 'getNeracaManualInputs'])->middleware('permission:LAP_NERACA_VIEW');
    Route::post('/dashboard/laporan/neraca/manual', [LaporanController::class, 'saveNeracaManualInputs'])->middleware('permission:LAP_NERACA_APPROVE');
    Route::get('/dashboard/laporan/export/neraca', [LaporanController::class, 'exportNeraca'])->middleware('permission:LAP_NERACA_EXPORT');
    Route::get('/dashboard/laporan/export/neraca-pdf', [LaporanController::class, 'exportNeracaPdf'])->middleware('permission:LAP_NERACA_EXPORT');

    Route::get('/dashboard/laporan/lo', [LaporanController::class, 'getLo'])->middleware('permission:LAP_LO_VIEW');
    Route::get('/dashboard/laporan/export/lo', [LaporanController::class, 'exportLo'])->middleware('permission:LAP_LO_EXPORT');
    Route::get('/dashboard/laporan/export/lo-pdf', [LaporanController::class, 'exportLoPdf'])->middleware('permission:LAP_LO_EXPORT');

    Route::get('/dashboard/laporan/lpe', [LaporanController::class, 'getLpe'])->middleware('permission:LAP_LPE_VIEW');
    Route::get('/dashboard/laporan/export/lpe', [LaporanController::class, 'exportLpe'])->middleware('permission:LAP_LPE_EXPORT');
    Route::get('/dashboard/laporan/export/lpe-pdf', [LaporanController::class, 'exportLpePdf'])->middleware('permission:LAP_LPE_EXPORT');

    Route::get('/dashboard/laporan/calk', [LaporanController::class, 'getCalk'])->middleware('permission:LAP_CALK_VIEW');
    Route::post('/dashboard/laporan/calk', [LaporanController::class, 'saveCalk'])->middleware('permission:LAP_CALK_APPROVE');
    Route::get('/dashboard/laporan/export/calk', [LaporanController::class, 'exportCalk'])->middleware('permission:LAP_CALK_EXPORT');
    Route::get('/dashboard/laporan/export/calk-pdf', [LaporanController::class, 'exportCalkPdf'])->middleware('permission:LAP_CALK_EXPORT');

    Route::get('/dashboard/laporan/lpsal', [LaporanController::class, 'getLpsal'])->middleware('permission:LAP_LPSAL_VIEW');
    Route::get('/dashboard/laporan/export/lpsal', [LaporanController::class, 'exportLpsal'])->middleware('permission:LAP_LPSAL_EXPORT');
    Route::get('/dashboard/laporan/export/lpsal-pdf', [LaporanController::class, 'exportLpsalPdf'])->middleware('permission:LAP_LPSAL_EXPORT');

    Route::get('/dashboard/laporan/rka', [LaporanController::class, 'getRka'])->middleware('permission:LAP_RKA_VIEW');
    Route::get('/dashboard/laporan/export/rka', [LaporanController::class, 'exportRka'])->middleware('permission:LAP_RKA_EXPORT');
    Route::get('/dashboard/laporan/export/rka-pdf', [LaporanController::class, 'exportRkaPdf'])->middleware('permission:LAP_RKA_EXPORT');

    Route::get('/dashboard/laporan/rba', [LaporanController::class, 'getRba'])->middleware('permission:LAP_RBA_VIEW');
    Route::get('/dashboard/laporan/export/rba', [LaporanController::class, 'exportRba'])->middleware('permission:LAP_RBA_EXPORT');
    Route::get('/dashboard/laporan/export/rba-pdf', [LaporanController::class, 'exportRbaPdf'])->middleware('permission:LAP_RBA_EXPORT');
});

Route::get(
    '/dashboard/pendapatan/anggaran',
    fn() => view('dashboard.pages.pendapatan.anggaran')
)->middleware(['auth', 'permission:ANGGARAN_PENDAPATAN_VIEW']);





