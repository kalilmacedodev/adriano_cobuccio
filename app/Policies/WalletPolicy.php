<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wallet;

class WalletPolicy
{
    public function deposit(User $user, Wallet $wallet)
    {
        // Só pode depositar na própria wallet
        return $user->id === $wallet->user_id;
    }

    public function transfer(User $user, Wallet $wallet)
    {
        // Só pode transferir da própria wallet
        return $user->id === $wallet->user_id;
    }
}
