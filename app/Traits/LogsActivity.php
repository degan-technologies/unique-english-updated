<?php

namespace App\Traits;

use App\Models\ActivityFeed;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    /**
     * Log user activity.
     *
     * @param string $type Activity type (e.g., 'login', 'enroll', 'complete').
     * @param string $action Short text describing the action.
     * @param string|null $description More details about the activity.
     * @param int|null $courseId Related course ID (if applicable).
     */
    public function logActivity($type, $action, $description = null, $courseId = null)
    {
        $user = Auth::user();

        if ($user) {
            ActivityFeed::create([
                'user_id' => $user->id,
                'course_id' => $courseId,
                'type' => $type,
                'action' => $action,
                'description' => $description,
            ]);
        }
    }

    
}
