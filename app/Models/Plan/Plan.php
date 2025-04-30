<?php

namespace App\Models\Plan;

use App\Models\Transaction\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model {
    use HasFactory;

    protected $fillable = [
        'slug',
        'user_id',
        'name', 
        'price', 
        'duration',
        'one_to_one_price', 
        'group_price',
    ];

    public function transactions() { return $this->hasMany(Transaction::class); }
}
