<?php

namespace App\Models\Notifications;

use App\Models\Transaction\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model {
    
    protected $fillable = [
        'data',
        'user_id', 
        'transaction_id'
    ]; 
    public function user() { return $this->belongsTo(User::class); }
    public function notifiableUsers() { return $this->hasMany(NotifiableUser::class); }
    public function transaction() { return $this->belongsTo(Transaction::class); }
}
