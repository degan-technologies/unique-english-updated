<?php

namespace App\Http\Controllers\Message;

use App\Helper\PhoneNumberHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Message\Message;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\LangService;
use App\Services\SMSService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Response;
class SMSController extends Controller
{
    protected $smsService;
    protected $langService;

    public function __construct(SMSService $smsService, LangService $langService)
    {
        $this->smsService = $smsService;
        $this->langService = $langService;
    }

    
    /**
     * Send a single message (SMS or Email) based on user region.
     */
    public function sendSMS(Request $request)
    {
        $validationRules = [
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('messages'));

        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->all()[0],
                'errors' => $validator->errors()
            ], 422);
        }

        // Retrieve the user
        $user = User::findOrFail($request->user_id);

        // Check if user has required contact info
        if (!$user->phone && !$user->email) {
            return response()->json(['error' => 'User does not have a valid phone number or email.'], 400);
        }

        // Create a new message record with 'pending' status
        $messageRecord = Message::create([
            'user_id' => $user->id,
            'message' => $request->message,
            'status' => 'pending',
        ]);

        $response = [];
        $newStatus = 'failed';

        // Determine sending method based on region
        if ($user->phone && Str::startsWith($user->phone, '+251')) {
            // Ethiopia: Send via SMS
            if (!$user->phone) {
                $response = ['success' => false, 'error' => 'User does not have a valid phone number.'];
            } else {
                $response = $this->smsService->sendSMS($user->phone, $request->message);
                $newStatus = $response['success'] ? 'sent' : 'failed';
            }
        } else {
            // Outside Ethiopia: Send via Email
            if (!$user->email) {
                $response = ['success' => false, 'error' => 'User does not have a valid email.'];
            } else {
                try {
                    Mail::raw($request->message, function ($mail) use ($user) {
                        $mail->to($user->email)
                             ->subject('Your Message');
                    });
                    $response = ['success' => true, 'data' => 'Email sent successfully'];
                    $newStatus = 'sent';
                } catch (\Exception $e) {
                    $response = ['success' => false, 'error' => $e->getMessage()];
                }
            }
        }

        // Update message status
        $messageRecord->update(['status' => $newStatus]);

        return response()->json([
            'message' => 'Message request processed',
            'status' => $messageRecord->status,
            'method' => Str::startsWith($user->phone, '+251') ? 'SMS' : 'Email',
            'response' => $response,
        ]);
    }

    /**
     * Send bulk messages (SMS or Email) based on user region.
     */
    public function sendBulkSMS(Request $request)
    {
        $validationRules = [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'message' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('messages'));

        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->all()[0],
                'errors' => $validator->errors()
            ], 422);
        }

        $users = User::whereIn('id', $request->user_ids)->get();
        $responses = [];

        foreach ($users as $user) {
            if (!$user->phone && !$user->email) {
                $responses[] = [
                    'user_id' => $user->id,
                    'status' => 'failed',
                    'method' => 'None',
                    'response' => 'User does not have a valid phone number or email.',
                ];
                continue;
            }

            // Create message with 'pending' status
            $messageRecord = Message::create([
                'user_id' => $user->id,
                'message' => $request->message,
                'status' => 'pending',
            ]);

            $response = [];
            $newStatus = 'failed';

            // Determine sending method based on region
            if ($user->phone && Str::startsWith($user->phone, '+251')) {
                // Ethiopia: Send via SMS
                if (!$user->phone) {
                    $response = ['success' => false, 'error' => 'User does not have a valid phone number.'];
                } else {
                    $response = $this->smsService->sendSMS($user->phone, $request->message);
                    $newStatus = $response['success'] ? 'sent' : 'failed';
                }
            } else {
                // Outside Ethiopia: Send via Email
                if (!$user->email) {
                    $response = ['success' => false, 'error' => 'User does not have a valid email.'];
                } else {
                    try {
                        Mail::raw($request->message, function ($mail) use ($user) {
                            $mail->to($user->email)
                                 ->subject('Your Message');
                        });
                        $response = ['success' => true, 'data' => 'Email sent successfully'];
                        $newStatus = 'sent';
                    } catch (\Exception $e) {
                        $response = ['success' => false, 'error' => $e->getMessage()];
                    }
                }
            }

            // Update message status
            $messageRecord->update(['status' => $newStatus]);

            $responses[] = [
                'user_id' => $user->id,
                'status' => $newStatus,
                'method' => Str::startsWith($user->phone, '+251') ? 'SMS' : 'Email',
                'response' => $response,
            ];
        }

        return response()->json(['bulk_message_status' => $responses]);
    }

    /**
     * Update an existing message (optional endpoint).
     */
    public function updateMessage(Request $request, $id)
    {
        $validationRules = [
            'message' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('messages'));

        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->all()[0],
                'errors' => $validator->errors()
            ], 422);
        }

        $messageRecord = Message::findOrFail($id);

        // Ensure only 'pending' messages can be updated
        if ($messageRecord->status !== 'pending') {
            return response()->json(['error' => 'Only pending messages can be updated.'], 403);
        }

        $messageRecord->update(['message' => $request->message]);

        return response()->json(['message' => 'Message updated successfully.', 'data' => $messageRecord]);
    }
    /**
    * Send an OTP to a specified phone number.
    *
    * @param \Illuminate\Http\Request $request
    * @return \Illuminate\Http\JsonResponse
    */
   public function sendOTP(Request $request)
   {
       // Validate the phone number input
       $validator = Validator::make($request->all(), [
           'phone' => 'required|string',
       ]);

       if ($validator->fails()) {
           return response()->json([
               'message' => $validator->errors()->first(),
           ], 422);
       }

       // Resolve SMSService instance
       $smsService = app(SMSService::class);

       // Send OTP using the sendSecurityCode method
       $result = $smsService->sendSecurityCode($request->phone);

       if ($result['success']) {
           return response()->json([
               'message' => 'OTP sent successfully',
           ], 200);
       } else {
           return response()->json([
               'message' => 'Failed to send OTP',
               'error' => $result['error'],
           ], 500);
       }
   }

   /**
    * Verify an OTP for a specified phone number.
    *
    * @param \Illuminate\Http\Request $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function verifyOtp(Request $request)
    {
        $validationRules = [
            'phone' => 'required|exists:users,phone',
            'otp' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('otp_verification'));

        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $formattedPhone = PhoneNumberHelper::formatPhoneNumber($request->phone); 
        // Using the helper
        $user = User::where('phone', $formattedPhone)->first();


        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        
        // Verify OTP using SMSService
        $smsService = new SMSService();
        $otpResponse = $smsService->verifySecurityCode($formattedPhone, $request->otp);

        if (!$otpResponse['success']) {
            return response()->json([
                'message' => 'OTP verification failed',
                'error' => $otpResponse['error'],
            ], 400);
        }

        // OTP verified successfully
        return response()->json([
            'message' => 'OTP verified successfully',
            'user' => new UserResource($user),
        ], 200);
    }
}