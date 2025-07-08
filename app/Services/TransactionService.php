<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;

class TransactionService
{
    public function create(Wallet $wallet, string $type, float $amount, ?User $relatedUser = null, ?string $description = null): Transaction
    {
        return $wallet->transactions()->create([
            'type' => $type,
            'amount' => $amount,
            'related_user_id' => $relatedUser?->id,
            'description' => $description,
        ]);
    }
}
