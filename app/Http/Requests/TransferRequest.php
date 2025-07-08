<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
   public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            // 'receiver_id' => ['required', 'exists:users,id', 'different:auth_user_id'],
            'receiver_id' => ['required', 'exists:users,id'],
        ];
    }
}
