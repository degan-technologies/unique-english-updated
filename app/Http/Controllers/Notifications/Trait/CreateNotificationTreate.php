<?php

namespace App\Http\Controllers\Notifications\Trait;

use App\Mail\AdminNotificationEmail;
use App\Mail\InstructorNotificationEmail;
use App\Mail\NotificationEmail;
use App\Mail\StudentNotificationEmail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

trait CreateNotificationTreate {

    public function enrollmentNotification($data, $transaction) { 
        $user =User::query()
            ->where('id', $transaction->customer_id)
            ->first(); 

        $systemAdmin = User::query()
            ->has('systemAdmin')
            ->first();

        $instructor = User::query()
            ->where('id', $transaction->user_id)
            ->first();

        $usersId = [$systemAdmin->id, $instructor->id, $user->id];

        try{
            DB::beginTransaction();

               $notification = $user->notifications()->create([
                    'data' => $data,
                    'transaction_id' => $transaction->id,
                ]);

                foreach($usersId as $userId){
                    $notification->notifiableUsers()->create([
                        'user_id' => $userId,
                    ]); 
                } 

                $instructorName = $instructor->first_name.' '.$instructor->middle_name;
                $studentName = $user->first_name.' '.$user->middle_name;


                Mail::to($instructor->email)->send(new InstructorNotificationEmail($instructorName, $studentName,$systemAdmin->email, $user->email, $transaction->created_at, $transaction->course->course_name));
                Mail::to( $systemAdmin->email)->send(new AdminNotificationEmail( $studentName, $user->email, $transaction->created_at, $transaction->course->course_name, $transaction->amount));
                Mail::to($userId->email)->send(new StudentNotificationEmail($studentName, $transaction->course->course_name));
                 
                DB::commit();
                return;
        }catch(\Exception $e){
            DB::rollback(); 
            return ;
        }
    }
}