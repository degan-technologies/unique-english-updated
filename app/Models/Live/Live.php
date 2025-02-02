<?php

namespace App\Models\Live;

use App\Models\Transaction\Transaction;
use Illuminate\Database\Eloquent\Model;

class Live extends Model {

    public function transactions() { return $this->hasMany(Transaction::class); }
}
