<?php

namespace App\Models\Message;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'message',
        'status',
    ];

    // Relationship: Each message belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessor to get the user's phone number dynamically
    public function getPhoneNumberAttribute()
    {
        return $this->user?->phone;
    }
}
