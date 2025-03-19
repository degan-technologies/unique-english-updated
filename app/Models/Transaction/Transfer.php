<?php

namespace App\Models\Transaction;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model {

    protected $fillable = [
        'account_number',
        'amount',
        'currency',
        'reference',
        'narration',
        'status',
        'user_id'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
