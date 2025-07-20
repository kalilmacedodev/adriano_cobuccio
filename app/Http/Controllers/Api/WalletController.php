<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\ReverseRequest;
use App\Http\Requests\TransferRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    public function deposit(DepositRequest $request): JsonResponse
    {
        try{

            $this->authorize('deposit', Auth::user()->wallet);

            $transaction = $this->walletService->deposit(Auth::user(), $request->validated('amount'));

            return response()->json([
                'message' => 'Depósito Efetuado com sucesso.',
                'transaction' => $transaction
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Exception $e) {
            // Qualquer outro erro inesperado
            return response()->json([
                'message' => 'Erro interno no servidor.',
                'error' => $e->getMessage(), // remova em produção por segurança
            ], 500);
        }
    }

    public function transfer(TransferRequest $request): JsonResponse
    {
        try{

            $this->authorize('transfer', Auth::user()->wallet);

            $receiverId = $request->validated('receiver_id');
            $amount = $request->validated('amount');

            if ($receiverId == Auth::id()) {
                return response()->json([
                    'message' => 'Não pode transferir para si mesmo.'
                ], 422);
            }

            $receiver = User::findOrFail($receiverId);

            $transaction = $this->walletService->transfer(Auth::user(), $receiver, $amount);

            return response()->json([
                'message' => 'Transferência realizada com Sucesso.',
                'transaction' => $transaction
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Exception $e) {
            // Qualquer outro erro inesperado
            return response()->json([
                'message' => 'Erro interno no servidor.',
                'error' => $e->getMessage(), // remova em produção por segurança
            ], 500);
        }
    }

    public function reverse(ReverseRequest $request): JsonResponse
    {
        try{

            $transaction = Transaction::findOrFail($request->validated('transaction_id'));

            // Opcional: garantir que só o dono da transação possa reverter
            if ($transaction->wallet->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $reversal = $this->walletService->reverse($transaction);

            return response()->json([
                'message' => 'Transação Revertida com Sucesso',
                'transaction' => $reversal
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Exception $e) {
            // Qualquer outro erro inesperado
            return response()->json([
                'message' => 'Erro interno no servidor.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
