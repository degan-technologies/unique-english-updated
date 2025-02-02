<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model {

    protected $fillable = [
        'slug', 'amount', 'transaction_type', 'status', 'payment_method', 'product_type',
        'tx_ref', 'payment_url', 'enrolled_at', 'user_id',
        'customer_id', 'course_id','book_id', 'live_id'
    ];

    public function enrollments() { return $this->morphTo(); }
}
