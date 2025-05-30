<?php

namespace App\Http\Controllers\Notifications\Trait;

use App\Mail\AdminNotificationEmail;
use App\Mail\InstructorNotificationEmail;
use App\Mail\StudentNotificationEmail;
use App\Models\Notifications\NotifiableUser;
use App\Models\Notifications\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

trait CreateNotificationTrait {

    public function enrollmentNotification($data, $transaction) {
        try {
            DB::beginTransaction();
 
            $transaction->load(['customer', 'user', 'course']);
            
            $user = $transaction->customer;
            $instructor = $transaction->user;
            $courseName = $transaction->course->course_name ?? 'Unknown Course';
 
            $recipients = [
                'instructor' => $instructor,
                'student' => $user,
            ];
 
            if ($instructor->user_id) {
                $systemAdmin = User::find($instructor->user_id);
                if ($systemAdmin) {
                    $recipients['admin'] = $systemAdmin;
                }
            }
 
            $notification =  Notification::create([
                'user_id' => $user->id,
                'data' => $data,
                'transaction_id' => $transaction->id,
            ]);
 
            foreach ($recipients as $recipient) {
                NotifiableUser::create([
                    'user_id' => $recipient->id,   
                    'notification_id' => $notification->id,
                ]);
            }
 
            $instructorName = $this->formatName($instructor);
            $studentName = $this->formatName($user);

            // Send emails in parallel using queue
            $this->sendNotificationEmails($recipients, [
                'instructorName' => $instructorName,
                'studentName' => $studentName,
                'courseName' => $courseName,
                'transaction' => $transaction
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'notification' => $notification
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Enrollment notification failed: '.$e->getMessage(), [
                'transaction_id' => $transaction->id ?? null,
                'error' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Notification processing failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    protected function formatName(User $user): string
    {
        return trim($user->first_name.' '.($user->middle_name ?? ''));
    }

    protected function sendNotificationEmails(array $recipients, array $data): void
    {
        // Send to instructor
        Mail::to($recipients['instructor']->email)
            ->queue(new InstructorNotificationEmail(
                $data['instructorName'],
                $data['studentName'],
                $recipients['admin']->email ?? null,
                $recipients['student']->email,
                $data['transaction']->created_at,
                $data['courseName']
            ));

        // Send to admin if exists
        if (isset($recipients['admin'])) {
            Mail::to($recipients['admin']->email)
                ->queue(new AdminNotificationEmail(
                    $data['studentName'],
                    $recipients['student']->email,
                    $data['transaction']->created_at,
                    $data['courseName'],
                    $data['transaction']->amount
                ));
        }

        // Send to student
        Mail::to($recipients['student']->email)
            ->queue(new StudentNotificationEmail(
                $data['studentName'],
                $data['courseName']
            ));
    }
}