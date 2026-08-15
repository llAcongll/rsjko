<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SafeSpaceApiController;
use App\Models\School;

Route::middleware(\App\Http\Middleware\VerifySafeSpaceToken::class)->group(function () {

    // Simpan hasil skrining
    Route::post('/safe-space/screenings', [SafeSpaceApiController::class, 'store']);

    // Daftar sekolah aktif untuk bot WhatsApp
    Route::get('/safe-space/schools', function () {
        return response()->json(
            School::select('id', 'code', 'name')
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
        );
    });

});
