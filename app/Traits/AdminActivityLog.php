<?php

namespace App\Traits;

use App\Http\Resources\Logs\AdminActivityLogResource;
use App\Models\ActivityFeed;
use App\Models\User;
use Illuminate\Http\Request;
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

    public function studentActivities($activity) {

        $user = User::query()
            ->has('student')
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

    public function getActivityLogs(Request $request) {

        $id = $request->query('id');
        /**
         * @var User User
         */
        

         if($id == null) {
            $id = Auth::id();
         }

        $user = User::query()
            ->where('id', $id)
            ->first();

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
