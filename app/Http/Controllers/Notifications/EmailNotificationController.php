<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Resources\Email\EmailAnnouncementResource;
use App\Models\User;
use App\Services\LangService;
use App\Services\SMSService;
use App\Traits\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class EmailNotificationController extends Controller {

    use AdminActivityLog;
    protected $smsService;
    protected $langService;

    public function __construct(SMSService $smsService, LangService $langService)
    {
        $this->smsService = $smsService;
        $this->langService = $langService;
    }
    /**
     * Send an email notification to a user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendEmailNotification(Request $request) {
        // Verify admin permissions
        $admin = User::query()
            ->has('systemAdmin')
            ->where('id', Auth::id())
            ->firstOrFail();

        // Validate input
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string',
            'message' => 'required|string',
        ], $this->langService->getLang('email_notification'));

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // Get all users (chunk for large datasets)
        $users = User::cursor();
        if ($users->count() === 0) {
            return response()->json([
                'message' => 'No users found'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Create notification record
            $notificationId = DB::table('email_notifications')->insert([
                'user_id' => $admin->id,
                'subject' => $request->subject,
                'user_ids' => json_encode(User::pluck('id')->all()),
                'message' => $request->message,
                'status' => 'processing',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            $failedEmails = [];
            $successCount = 0;

            foreach ($users as $user) {
                try {
                    Mail::raw($request->message, function ($mail) use ($user, $request) {
                        $mail->to($user->email)
                            ->subject($request->subject);
                    });
                    $successCount++;
                } catch (\Exception $e) {
                    $failedEmails[] = [
                        'email' => $user->email,
                        'error' => $e->getMessage()
                    ];
                    logger()->error('Email send failed to '.$user->email, ['error' => $e]);
                }
            }

            // Update notification status
            $status = empty($failedEmails) ? 'sent' : ($successCount > 0 ? 'partial' : 'failed');
            
            DB::table('email_notifications')
                ->where('id', $notificationId)
                ->update([
                    'status' => $status,
                    'updated_at' => now()
                ]);

            DB::commit();

            $this->adminActivities('send message');

            return response()->json([
                'message' => 'Emails processed',
                'data' => [
                    'success_count' => $successCount,
                    'failed_count' => count($failedEmails),
                    'status' => $status
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Email notification failed', ['error' => $e]);
            
            return response()->json([
                'message' => 'Failed to process email notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAnnouncements(Request $request) {
        $user = User::query()
            ->has('systemAdmin')
            ->where('id', Auth::id())
            ->first();


        $emailNotifications = DB::table('email_notifications')
            ->where('user_id', $user->id)
            ->orderBy('created_at')
            ->get();

        if (!$emailNotifications) {
            return response()->json([
               'message' => 'No notifications found'
            ], 404);
        }

        return response()->json([
            'data' => EmailAnnouncementResource::collection($emailNotifications)
        ]);
    }
}
