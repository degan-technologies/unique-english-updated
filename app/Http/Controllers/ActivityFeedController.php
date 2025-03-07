<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityFeed;
use Illuminate\Support\Facades\Auth;

class ActivityFeedController extends Controller
{
    /**
     * Fetch the activity feed for the authenticated user.
     */
    public function index(Request $request)
    {
        $activities = ActivityFeed::with('user:id,first_name,middle_name')
            ->get();

        return response()->json($activities);
    }
}
