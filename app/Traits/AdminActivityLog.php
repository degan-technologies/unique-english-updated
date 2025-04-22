<?php

namespace App\Traits;

use App\Http\Resources\Logs\AdminActivityLogResource;
use App\Models\ActivityFeed;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait AdminActivityLog {
    /**
     * Log user activity.
     *
     * @param string $type Activity type (e.g., 'login', 'enroll', 'complete').
     * @param string $action Short text describing the action.
     * @param string|null $description More details about the activity.
     * @param int|null $courseId Related course ID (if applicable).
     */
    public function adminActivities($activity) {

        $user = User::query()
            ->has('systemAdmin')
            ->where('id', Auth::id())
            ->first();

        if (!$user) {
            return;
        }

        $user->adminActivityLogs()->create([
            'activity' => $activity,
        ]);

        return;         
    }


    /**
     * Get the activity logs for the authenticated user.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */

    public function getActivityLogs() {

        /**
         * @var User User
         */

        $user =Auth::user();

        $logs =  $user->adminActivityLogs()
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        if ($logs->isEmpty()) {
            return response()->json([
                'message' => 'No activity logs found.'
            ], 404);
        }

        return response()->json([
            'data' => AdminActivityLogResource::collection($logs),
        ]);
    }
}
