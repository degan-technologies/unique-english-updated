<?php

namespace App\Http\Controllers;

use App\Http\Resources\Notification\UserNotificationResource;
use App\Models\Notifications\NotifiableUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;  


class NotificationController extends Controller {
    public function index() {
       $notifiedUser = NotifiableUser::query()
            ->where('user_id', Auth::user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $unReadNotifications = $notifiedUser->where('read_at', null)->count();

        return response()->json([
            'data' => UserNotificationResource::collection($notifiedUser),
            'unReadNotifications'=> $unReadNotifications
        ]);
    }

    public function unread()
    {
        return response()->json(Auth::user()->unreadNotifications);
    }

    public function markAsRead($id) {  
        $user = Auth::user(); 

        $notifiableUser = NotifiableUser::query()
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->first();  

        if (!$notifiableUser) {
            return response()->json([
                'status' => 'not_found'
            ], 404);
        } 
        
        $notifiableUser->update([
            'read_at' => Carbon::now(),
        ]);

        $unReadNotifications =NotifiableUser::query()
            ->where('user_id', $user->id)
            ->where('read_at', null)
            ->get()
            ->count();  

        return response()->json([
            'data'=> new UserNotificationResource($notifiableUser),
            'unReadNotifications'=> $unReadNotifications
        ]);
    }
}
