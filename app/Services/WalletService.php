<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Log;

class WalletService
{
    public function __construct(
        protected TransactionService $transactionService
    ) {}

    public function deposit(User $user, float $amount): Transaction
    {
        return DB::transaction(function () use ($user, $amount) {
            $wallet = $user->wallet;

            if ($wallet->balance < 0) {

                Log::warning('Tentativa de depósito em carteira com saldo negativo', [
                    'wallet_id' => $wallet->id,
                    'user_id' => $wallet->user_id,
                    'balance' => $wallet->balance,
                    'timestamp' => now(),
                ]);

                throw ValidationException::withMessages([
                    'balance' => 'Não se pode depositar em carteira com saldo negativo.',
                ]);
            }

            $wallet->balance += $amount;
            $wallet->save();

            Log::info("Depósito realizado", [
                'user_id' => $user->id,
                'amount' => $amount,
                'timestamp' => now(),
            ]);

            return $this->transactionService->create($wallet, 'deposit', $amount);
        });
    }

    public function transfer(User $sender, User $receiver, float $amount): Transaction
    {
        return DB::transaction(function () use ($sender, $receiver, $amount) {
            $senderWallet = $sender->wallet;
            $receiverWallet = $receiver->wallet;

            if ($senderWallet->balance < $amount) {

                Log::warning('Transferência falhou por saldo insuficiente', [
                    'sender_id' => $sender->id,
                    'receiver_id' => $receiver->id,
                    'amount' => $amount,
                    'sender_balance' => $senderWallet->balance,
                    'timestamp' => now(),
                ]);

                throw ValidationException::withMessages([
                    'balance' => 'Saldo insuficiente.',
                ]);
            }

            $senderWallet->balance -= $amount;
            $receiverWallet->balance += $amount;

            $senderWallet->save();
            $receiverWallet->save();

            Log::info('Transferência realizada com sucesso', [
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->d,
                'amount' => $amount,
                'sender_balance_after' => $senderWallet->balance,
                'receiver_balance_after' => $receiverWallet->balance,
                'timestamp' => now(),
            ]);

            return $this->transactionService->create($senderWallet, 'transfer', $amount, $receiver);
        });
    }

    public function reverse(Transaction $transaction): Transaction
    {
        if ($transaction->reversed) {

            Log::warning('Tentativa de reversão já realizada', [
                'transaction_id' => $transaction->id,
                'wallet_id' => $transaction->wallet_id,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'timestamp' => now(),
            ]);

            throw ValidationException::withMessages([
                'transaction' => 'Transação já foi revertida.',
            ]);
        }

        return DB::transaction(function () use ($transaction) {
            $wallet = $transaction->wallet;

            if ($transaction->type === 'deposit') {

                if ($wallet->balance < $transaction->amount) {
                    Log::error('Saldo insuficiente para reverter depósito', [
                        'wallet_id' => $wallet->id,
                        'transaction_id' => $transaction->id,
                        'balance' => $wallet->balance,
                        'amount' => $transaction->amount,
                        'timestamp' => now(),
                    ]);

                    throw new \Exception('Saldo insuficiente para reverter depósito.');
                }

                $wallet->balance -= $transaction->amount;

            } elseif ($transaction->type === 'transfer') {

                $wallet->balance += $transaction->amount;

                $receiver = User::find($transaction->related_user_id);
                $receiver->wallet->balance -= $transaction->amount;
                $receiver->wallet->save();
            }

            $wallet->save();

            $transaction->update(['reversed' => true]);

            Log::info('Transação revertida com sucesso', [
                'transaction_id' => $transaction->id,
                'wallet_id' => $wallet->id,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'timestamp' => now(),
            ]);

            return $this->transactionService->create($wallet, 'reversal', $transaction->amount, null, 'Reversão da Transação #' . $transaction->id);
        });
    }
}
