<?php

namespace App\Models\Bank;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BankInfo extends Model {
    
    protected $fillable = [
        'full_name',
        'bank_name',
        'bank_code',
        'account_number',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
