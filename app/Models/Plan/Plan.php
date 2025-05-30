<?php

namespace App\Models\Plan;

use App\Models\Transaction\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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

    protected $casts = [
        'price' => 'integer',
        'one_to_one_price' => 'integer',
        'group_price' => 'integer',
    ]; 

    public function transactions() { return $this->hasMany(Transaction::class); }

    public static function checkEligibility($planId) {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return false;
        }

        $myTransactions = Transaction::query()
            ->where('customer_id', $user->id)
            ->where('plan_id', $planId)
            ->where('product_type', LIVE_CLASS)
            ->where('status', TRANSACTION_SUCCESS)
            ->first();

        if ($myTransactions) {
            return true;
        }

        return false;
    }
}
