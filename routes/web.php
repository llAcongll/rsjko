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
use App\Http\Controllers\PendapatanLainController;
use App\Http\Controllers\PendapatanKerjasamaController;
use App\Http\Controllers\KodeRekeningController;
use App\Http\Controllers\AnggaranRekeningController;
use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\MouController;
use App\Http\Controllers\PiutangController;
use App\Http\Controllers\PenyesuaianPendapatanController;

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

/*
|--------------------------------------------------------------------------
| DASHBOARD (BASE & AJAX)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/', fn() => redirect()->route('dashboard'));

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

        Route::middleware('role:ADMIN')
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

        Route::middleware('role:ADMIN')
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

        Route::middleware('role:ADMIN')
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
| REKENING KORAN (ADMIN ONLY)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:ADMIN,USER'])
    ->prefix('dashboard/rekening-korans')
    ->group(function () {

        Route::get('/template', [RekeningKoranController::class, 'downloadTemplate']);
        Route::post('/import', [RekeningKoranController::class, 'import']);
        Route::delete('/bulk-delete', [RekeningKoranController::class, 'bulkDelete']);
        Route::get('/', [RekeningKoranController::class, 'index']);
        Route::post('/', [RekeningKoranController::class, 'store']);
        Route::get('/{rekeningKoran}', [RekeningKoranController::class, 'show']);
        Route::put('/{rekeningKoran}', [RekeningKoranController::class, 'update']);
        Route::delete('/{rekeningKoran}', [RekeningKoranController::class, 'destroy']);
    });

Route::middleware(['auth', 'role:ADMIN,USER'])->group(function () {

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
    });

/*
|--------------------------------------------------------------------------
| VIEW — MODE ANGGARAN (READ ONLY)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard/laporan/data', [LaporanController::class, 'index'])->middleware('auth');
Route::get('/dashboard/laporan/export/pendapatan', [LaporanController::class, 'export'])->middleware('auth');
Route::get('/dashboard/laporan/export/pendapatan-pdf', [LaporanController::class, 'exportPdf'])->middleware('auth');
Route::get('/dashboard/laporan/rekon', [LaporanController::class, 'getRekon'])->middleware('auth');
Route::get('/dashboard/laporan/piutang', [LaporanController::class, 'getPiutang'])->middleware('auth');
Route::get('/dashboard/laporan/mou', [LaporanController::class, 'getMou'])->middleware('auth');
Route::get('/dashboard/laporan/anggaran', [LaporanController::class, 'getAnggaran'])->middleware('auth');

Route::get(
    '/dashboard/pendapatan/anggaran',
    fn() => view('dashboard.pages.pendapatan.anggaran')
)->middleware('auth');
