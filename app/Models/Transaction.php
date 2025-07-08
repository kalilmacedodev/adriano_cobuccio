<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = "transactions";

    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'description',
        'related_user_id',
        'reversed',
    ];

    protected $casts = [
        'reversed' => 'boolean',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id', 'id');
    }

    public function relatedUser()
    {
        return $this->belongsTo(User::class, 'related_user_id', 'id');
    }
}
