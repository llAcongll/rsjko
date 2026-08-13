<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SafeSpaceApiController;

Route::middleware(\App\Http\Middleware\VerifySafeSpaceToken::class)->group(function () {
    Route::post('/safe-space/screenings', [SafeSpaceApiController::class, 'store']);
});
