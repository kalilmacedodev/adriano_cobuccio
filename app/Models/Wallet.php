<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $table = "wallets";

    protected $fillable = [
        'user_id',
        'balance',
    ];

    public function transactions(){
        return $this->hasMany(Transaction::class, 'wallet_id', 'id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
