<?php

namespace App\Models\Comment;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class FeedbackUserInteraction extends Model
{
    // Define the table name if it doesn't follow Laravel's naming convention.
    protected $table = 'feedback_user_interactions';

    // The attributes that are mass assignable.
    protected $fillable = [
        'user_id',
        'feed_back_id',
        'favorite',
        'reported',
    ];

    // Cast the boolean fields.
    protected $casts = [
        'liked'    => 'boolean',
        'disliked' => 'boolean',
        'reported' => 'boolean',
    ];

    /**
     * Relationship to the User model.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship to the FeedBack model.
     */
    public function feedback()
    {
        return $this->belongsTo(FeedBack::class);
    }
}
