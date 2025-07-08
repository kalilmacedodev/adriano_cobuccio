<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WalletController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

Route::post('/login', function (Request $request) {
    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Credenciais inválidas'], 401);
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json(['token' => $token]);
});

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::post('/wallet/deposit', [WalletController::class, 'deposit']);
    Route::post('/wallet/transfer', [WalletController::class, 'transfer']);
    Route::post('/wallet/reverse', [WalletController::class, 'reverse']);
});
