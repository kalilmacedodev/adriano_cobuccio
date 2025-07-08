<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WalletApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Wallet $wallet;

    protected function setUp(): void
    {

        parent::setUp();

        $this->user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'balance' => 100.00,
        ]);

        Sanctum::actingAs($this->user);
    }

    public function test_user_can_deposit_successfully(): void
    {

        $response = $this->postJson('/api/wallet/deposit', [
            'amount' => 50.00,
        ]);

        $response->assertStatus(201);
    }

    public function test_user_can_transfer_money(): void
    {
        $receiver = User::factory()->create();
        Wallet::factory()->create([
            'user_id' => $receiver->id,
            'balance' => 20.00,
        ]);

        $response = $this->postJson('/api/wallet/transfer', [
            'amount' => 30.00,
            'receiver_id' => $receiver->id,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('wallets', [
            'id' => $this->wallet->id,
            'balance' => 70.00,
        ]);
    }

    public function test_transfer_fails_with_insufficient_balance(): void
    {
        $receiver = User::factory()->create();
        Wallet::factory()->create(['user_id' => $receiver->id]);

        $response = $this->postJson('/api/wallet/transfer', [
            'amount' => 9999.00,
            'receiver_id' => $receiver->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('balance');
    }

    public function test_transaction_can_be_reversed(): void
    {
        $transaction = Transaction::create([
            'wallet_id' => $this->wallet->id,
            'type' => 'deposit',
            'amount' => 50.00,
        ]);

        $this->wallet->increment('balance', 50.00);

        $response = $this->postJson('/api/wallet/reverse', [
            'transaction_id' => $transaction->id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'reversed' => true,
        ]);
    }

    public function test_user_cannot_deposit_if_not_authorized(): void
    {
        // Criar usuário dono da carteira
        $owner = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Criar outra carteira para o dono
        $wallet = Wallet::factory()->create([
            'user_id' => $owner->id,
            'balance' => 100.00,
        ]);

        // Criar outro usuário que tentará depositar (não autorizado)
        $otherUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Autenticar como usuário não autorizado
        Sanctum::actingAs($otherUser);

        // Tentar depositar na carteira do dono (que não é do usuário logado)
        $response = $this->postJson('/api/wallet/deposit', [
            'amount' => 50.00,
            'wallet_id' => $wallet->id,
        ]);

        // Espera status 403 Forbidden por falta de autorização
        $response->assertForbidden();
    }

    public function test_user_cannot_deposit_with_negative_wallet_balance(): void
    {
        // Deixa a carteira com saldo negativo
        $this->wallet->update(['balance' => -50.00]);

        $response = $this->postJson('/api/wallet/deposit', [
            'amount' => 100.00,
        ]);

        // Restaura o valor
        $this->wallet->update(['balance' => 200.00]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('balance');
        $response->assertJsonFragment([
            'balance' => ['Não se pode depositar em carteira com saldo negativo.']
        ]);
    }

    public function test_user_cannot_transfer_with_insufficient_balance(): void
    {
        // Cria o destinatário com carteira
        $receiver = User::factory()->create();

        Wallet::factory()->create([
            'user_id' => $receiver->id,
            'balance' => 50.00,
        ]);

        // Tenta transferir mais do que o saldo disponível (saldo atual = 100.00)
        $response = $this->postJson('/api/wallet/transfer', [
            'amount' => 200.00,
            'receiver_id' => $receiver->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('balance');
        $response->assertJsonFragment([
            'balance' => ['Saldo insuficiente.']
        ]);
    }

    public function test_full_transaction_flow()
    {
        $receiver = User::factory()->create(['email_verified_at' => now()]);
        
        $receiverWallet = Wallet::factory()->create([
            'user_id' => $receiver->id,
            'balance' => 0,
        ]);

        // DEPÓSITO
        $depositResponse = $this->postJson('/api/wallet/deposit', [
            'amount' => 100.00,
        ]);
        $depositResponse->assertStatus(201);

        $this->wallet->refresh();
        $this->assertEquals(200.00, $this->wallet->balance); // saldo inicial era 100

        // TRANSFERÊNCIA
        $transferResponse = $this->postJson('/api/wallet/transfer', [
            'amount' => 50.00,
            'receiver_id' => $receiver->id,
        ]);
        $transferResponse->assertStatus(201);

        $this->wallet->refresh();
        $receiverWallet->refresh();
        $this->assertEquals(150.00, $this->wallet->balance);
        $this->assertEquals(50.00, $receiverWallet->balance);

        // REVERSÃO da transferência
        $lastTransaction = Transaction::where('type', 'transfer')->latest()->first();
        $reverseResponse = $this->postJson('/api/wallet/reverse', [
            'transaction_id' => $lastTransaction->id,
        ]);
        $reverseResponse->assertStatus(200);

        $this->wallet->refresh();
        $receiverWallet->refresh();
        $this->assertEquals(200.00, $this->wallet->balance);
        $this->assertEquals(0.00, $receiverWallet->balance);
    }
}
