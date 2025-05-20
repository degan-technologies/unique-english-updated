<?php

namespace App\Http\Resources\Notification;

use App\Models\Notifications\Notification;
use App\Models\Transaction\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserNotificationResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 
            'data' =>$this->createMessage($this->notification_id),
            'read_at' => $this->read_at,
            'created_at' => $this->created_at->format('D, F-d-Y'),
        ];
    } 

    public function createMessage($id) {

        /**
         * @var User
         */

        $productName = '';

        $user =  Auth::user();

        $notification = Notification::query()
            ->where('id', $id)
            ->first(); 

        $transaction = Transaction::query()
                ->where('id', $notification->transaction_id)
                ->first();

        $student = User::query()
        ->where('id', $transaction->customer_id)
        ->first();

        switch ($transaction->product_type) {

            case COURSE:
                $productName = $transaction->course->course_name;
                break;
            case BOOK:
                $productName = $transaction->book->title;
                break;

            case LIVE_CLASS:
                $productName = $transaction->plan->name;
                break;
            default: 
                break;
        }

        if($user->student){
            return [
                'title' => "Hello $user->first_name $user->middle_name!",
                'message' => "You payment for $productName has been completed with amount of $transaction->amount ETB, from $user->first_name $user->middle_name",
            ];
        } else {
            return [
                'title' => "Hello $user->first_name $user->middle_name!",
                'message' => "$productName has been purchased by $student->first_name $student->middle_name with amout of $transaction->amount ETB",
            ];
        }

    }
}
