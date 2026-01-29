<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\RekeningKoranController;
use App\Http\Controllers\PendapatanUmumController;

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login')
    ->middleware('guest');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('guest');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth');

/*
|--------------------------------------------------------------------------
| DASHBOARD (PROTECTED)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/', fn () => redirect()->route('dashboard'));

    Route::get('/dashboard', fn () => view('layouts.dashboard'))
        ->name('dashboard');

    Route::prefix('dashboard')->group(function () {

        // 🔹 PARTIAL CONTENT (AJAX VIEW)
        Route::get('/content/{page}/{param?}', [DashboardController::class, 'content']);

        // 🔐 USERS API (ADMIN ONLY, JSON)
        Route::middleware('role:ADMIN')->prefix('users')->group(function () {

            Route::get('/',         [UserController::class, 'index']);
            Route::post('/',        [UserController::class, 'store']);
            Route::get('/{user}',   [UserController::class, 'show']);
            Route::put('/{user}',   [UserController::class, 'update']);
            Route::delete('/{user}',[UserController::class, 'destroy']);

        });

    });
});

/*
|--------------------------------------------------------------------------
| RUANGAN (PROTECTED)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('dashboard')->group(function () {

    Route::middleware('role:ADMIN')->prefix('ruangans')->group(function () {

        Route::get('/', [RuanganController::class, 'index']);
        Route::post('/', [RuanganController::class, 'store']);
        Route::get('/next-kode', [RuanganController::class, 'nextKode']); // ✅ INI
        Route::get('/{ruangan}', [RuanganController::class, 'show']);
        Route::put('/{ruangan}', [RuanganController::class, 'update']);
        Route::delete('/{ruangan}', [RuanganController::class, 'destroy']);

    });

});

Route::middleware('auth')
    ->prefix('dashboard')
    ->group(function () {

        Route::get('/ruangan-list', [RuanganController::class, 'list']);

    });

Route::middleware(['auth','role:ADMIN'])
    ->prefix('dashboard')
    ->group(function () {

        Route::prefix('rekening-korans')->group(function () {
            Route::get('/',        [RekeningKoranController::class, 'index']);
            Route::post('/',       [RekeningKoranController::class, 'store']);
            Route::get('/{rekeningKoran}', [RekeningKoranController::class, 'show']);
            Route::put('/{rekeningKoran}', [RekeningKoranController::class, 'update']);
            Route::delete('/{rekeningKoran}', [RekeningKoranController::class, 'destroy']);
        });

});

Route::middleware('auth')
  ->prefix('dashboard/pendapatan/umum')
  ->group(function () {

    Route::get('/', [PendapatanUmumController::class, 'index']);   // list
    Route::post('/', [PendapatanUmumController::class, 'store']); // tambah

    Route::get('/{id}', [PendapatanUmumController::class, 'show']); // 🔥 EDIT / DETAIL
    Route::put('/{id}', [PendapatanUmumController::class, 'update']); // 🔥 UPDATE
    Route::delete('/{id}', [PendapatanUmumController::class, 'destroy']); // delete

});
