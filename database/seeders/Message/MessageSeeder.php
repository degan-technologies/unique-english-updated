<?php

namespace Database\Seeders\Message;

use App\Models\Message\Message;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Services\SMSService;

class MessageSeeder extends Seeder
{
    protected $smsService;

    public function __construct()
    {
        // Initialize the SMS service
        $this->smsService = app(SMSService::class);
    }

    public function run()
    {
        if (User::count() == 0) {
            $this->command->info('No users found. Please run UserSeeder first.');
            return;
        }

        // Fetch random users for individual messages
        $users = User::inRandomOrder()->take(5)->get();

        foreach ($users as $user) {
            $messageContent = "Hello, {$user->first_name}! This is a test message.";

            // Create pending message
            $messageRecord = Message::create([
                'user_id' => $user->id,
                'message' => $messageContent,
                'status'  => 'pending',
            ]);

            // Simulate SMS sending
            $response = $this->smsService->sendSMS($user->phone, $messageContent);

            // Update message status based on SMS response
            $newStatus = $response['success'] ? 'sent' : 'failed';
            $messageRecord->update(['status' => $newStatus]);
        }

        // Simulate bulk message sending
        $bulkUsers = User::inRandomOrder()->take(3)->get();
        $bulkMessageContent = "This is a bulk message for all users.";

        foreach ($bulkUsers as $user) {
            $messageRecord = Message::create([
                'user_id' => $user->id,
                'message' => $bulkMessageContent,
                'status'  => 'pending',
            ]);

            $response = $this->smsService->sendSMS($user->phone, $bulkMessageContent);
            $newStatus = $response['success'] ? 'sent' : 'failed';

            $messageRecord->update(['status' => $newStatus]);
        }

        $this->command->info('Messages (including bulk) seeded successfully.');
    }
}
