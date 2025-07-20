<?php

use App\Http\Controllers\Api\AuthApiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WalletController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

Route::middleware('throttle:60,1')->group(function () {

    Route::post('/login', [AuthApiController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/wallet/deposit', [WalletController::class, 'deposit']);
        Route::post('/wallet/transfer', [WalletController::class, 'transfer']);
        Route::post('/wallet/reverse', [WalletController::class, 'reverse']);
    });

});
