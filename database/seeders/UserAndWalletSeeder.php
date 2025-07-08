<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Hash;

class UserAndWalletSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $user = User::create([
                'name' => "Usuário {$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password'), // senha padrão
                // se tiver verificação de email:
                'email_verified_at' => now(),
            ]);

            $user->wallet()->create([
                'balance' => rand(100, 1000) / 10, // saldo aleatório ex: 32.5
            ]);
        }
    }
}
