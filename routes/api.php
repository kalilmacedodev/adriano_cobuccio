<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WalletController;

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::post('/wallet/deposit', [WalletController::class, 'deposit']);
    Route::post('/wallet/transfer', [WalletController::class, 'transfer']);
    Route::post('/wallet/reverse', [WalletController::class, 'reverse']);
});
