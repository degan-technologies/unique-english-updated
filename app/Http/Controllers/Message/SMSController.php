<?php

namespace App\Http\Controllers\Message;

use App\Http\Controllers\Controller;
use App\Models\Message\Message;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\LangService;
use App\Services\SMSService;
use Illuminate\Support\Facades\Validator;

class SMSController extends Controller
{
    protected $smsService;

    protected $langService;

    public function __construct(SMSService $smsService, LangService $langService)
    {
        $this->smsService = $smsService;
        $this->langService = $langService;
    }
    // Send a single SMS and update the message status
    public function sendSMS(Request $request)
    {
        
        $validationRules=[
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('messages'));

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors'  => $validator->errors()
            ], 422);
        }

        // Retrieve the user and their phone number
        $user = User::findOrFail($request->user_id);

        // Ensure the user has a phone number
        if (!$user->phone) {
            return response()->json(['error' => 'User does not have a valid phone number.'], 400);
        }

        // Create a new message record with 'pending' status
        $messageRecord = Message::create([
            'user_id' => $user->id,
            'message' => $request->message,
            'status'  => 'pending',
        ]);

        // Send the SMS using the SMSService
        $response = $this->smsService->sendSMS($user->phone, $request->message);

        // Update message status based on the SMS service response
        $newStatus = $response['success'] ? 'sent' : 'failed';
        $messageRecord->update(['status' => $newStatus]);

        return response()->json([
            'message'  => 'SMS request processed',
            'status'   => $messageRecord->status,
            'response' => $response,
        ]);
    }

    // Send bulk SMS and update the message statuses
    public function sendBulkSMS(Request $request)
    {
        $validationRules=[
            'user_ids'   => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'message'    => 'required|string',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('messages'));

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors'  => $validator->errors()
            ], 422);
        }

        $users = User::whereIn('id', $request->user_ids)->get();
        $responses = [];

        foreach ($users as $user) {
            if (!$user->phone) {
                $responses[] = [
                    'user_id'  => $user->id,
                    'status'   => 'failed',
                    'response' => 'User does not have a valid phone number.',
                ];
                continue;
            }

            // Create message with 'pending' status
            $messageRecord = Message::create([
                'user_id' => $user->id,
                'message' => $request->message,
                'status'  => 'pending',
            ]);

            // Send the SMS
            $response = $this->smsService->sendSMS($user->phone, $request->message);

            // Update message status
            $newStatus = $response['success'] ? 'sent' : 'failed';
            $messageRecord->update(['status' => $newStatus]);

            $responses[] = [
                'user_id'  => $user->id,
                'status'   => $newStatus,
                'response' => $response,
            ];
        }

        return response()->json(['bulk_sms_status' => $responses]);
    }

    // Update an existing message (optional endpoint)
    public function updateMessage(Request $request, $id)
    {
        
        $validationRules=[
            'message' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('messages'));

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors'  => $validator->errors()
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
}
