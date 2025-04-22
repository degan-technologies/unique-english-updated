<?php

namespace App\Models\Notifications;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class EmailNotification extends Model {
    
    protected $fillable = [
        'subject',
        'message',
        'user_ids',
        'not_deleted',
        'user_id',
    ];

    protected $casts = [
        'user_ids' => 'array',  
    ];
 
    public function user() {
        return $this->belongsTo(User::class);
    }
}
