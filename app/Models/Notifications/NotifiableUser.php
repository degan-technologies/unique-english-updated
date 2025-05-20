<?php

namespace App\Models\Notifications;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class NotifiableUser extends Model {
    
    protected $fillable = [
        'notification_id',
        'user_id', 
        'read_at',
    ]; 
    public function user() { return $this->belongsTo(User::class); }
    public function notifications() { return $this->belongsTo(Notification::class); }
}
