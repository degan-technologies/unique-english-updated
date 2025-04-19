<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;


class NotificationController extends Controller
{
    public function index()
    {
        return response()->json(Auth::user()->notifications);
    }

    public function unread()
    {
        return response()->json(Auth::user()->unreadNotifications);
    }

    public function markAsRead($id) {  
        $user = Auth::user(); 
        $notifications = $user->notifications;
      
        $notification = $notifications->firstWhere('id', $id);
      
        if (!$notification) {
            return response()->json([
                'status' => 'not_found'
            ], 404);
        } 
        
        $notification->markAsRead();

        return response()->json([
            'status' => 'success',
            'read_at' => $notification->read_at,
        ]);
    
    }
    
    

    
    
    
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['status' => 'all_marked_as_read']);
    }
}
