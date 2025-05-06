<?php

namespace App\Models\Transaction;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model {

    protected $fillable = [
        'currency',
        'reference',
        'status',
        'deposits',
        'withdrawals',
        'user_id',
        'transaction_id',
        'balance',
    ];

    public function user() { return $this->belongsTo(User::class); }

    public function transaction() { return $this->belongsTo(Transaction::class); }
}
