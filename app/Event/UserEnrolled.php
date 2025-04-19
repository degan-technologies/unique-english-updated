<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class UserEnrolled implements ShouldBroadcast
{
    use SerializesModels;

    public $user;
    public $course;

    public function __construct($user, $course)
    {
        $this->user = $user;
        $this->course = $course;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('users.' . $this->user->id);
    }

    public function broadcastWith()
    {
        return [
            'message' => "You have enrolled in {$this->course->name}."
        ];
    }
}
