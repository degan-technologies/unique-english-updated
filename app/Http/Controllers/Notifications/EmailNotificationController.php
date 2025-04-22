<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LangService;
use App\Services\SMSService;
use App\Traits\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

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
    public function sedEmailNotification(Request $request) {
 
        $newStatus = 'failed';
        $admin = User::query()
            ->has('systemAdmin')
            ->where('id', Auth::id())
            ->first();

        if (!$admin) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $validationRules = [
            'subject' => 'required|string ',
            'message' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('email_notification'));

        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->all()[0],
                'errors' => $validator->errors()
            ], 422);
        }
        $users = User::all();

        if ($users->isEmpty()) {
            return response()->json([
                'message' => 'no user not found'
            ], 400);
        }

        DB::beginTransaction();

        $messageRecord = $admin->emailNotifications()->create([
            'subject' => $request->subject,
            'user_ids' => json_encode($users->pluck('id')->toArray()),
            'message' => $request->message, 
        ]);

        foreach ($users as $user) {
            try {
                Mail::raw($request->message, function ($mail) use ($user, $request) {
                    $mail->to($user->email)
                        ->subject($request->subject);
                }); 

                $newStatus = 'sent'; 
            } catch (\Exception $e) {

                $newStatus = 'failed';
                DB::rollBack();

                return response()->json([
                    'message' => 'Failed to send email to ' . $user->email,
                    'error' => $e->getMessage()
                ], 500);
            }            
        }
        
        $messageRecord->update(['status' => $newStatus]);
        $this->adminActivities('send message');

        DB::commit();

        return response()->json([
            'message' => 'Message request processed', 
        ]);
    }

    public function getAnnouncements(Request $request) {
        $user = User::query()
            ->has('systemAdmin')
            ->where('id', Auth::id())
            ->first();


        $emailNotifications = $user->emailNotifications()->orderBy('created_at')->get();

        return response()->json([
            'message' => 'Email notifications retrieved successfully',
            'data' => $emailNotifications
        ]);
    }
}
