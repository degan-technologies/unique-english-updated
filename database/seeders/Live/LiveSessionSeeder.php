<?php

namespace Database\Seeders\Live;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Live\LiveSession;
use App\Models\User;

class LiveSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $instructor = User::query()
            ->whereSystemAdminOrInstructor()
            ->first(); 

        if ($instructor) {
            $sessions = [
                [

                    'slug' => Str::uuid(),
                    'title' => 'Advanced English Grammar: The Subjunctive Mood',
                    'start_time' => now()->addDays(1)->setTime(14, 0), // Next day at 2 PM
                    'end_time' => now()->addDays(1)->setTime(16, 0),   // Ends at 4 PM
                    'description' => 'A deep dive into the complexities of the subjunctive mood in English grammar.',
                    'user_id' => $instructor->id,
                    'status' => 'live',
                    'max_participants' => 50,
                    'stream_url' => 'https://example.com/stream/advanced-english-grammar',
                ],
                [
                    'slug' => Str::uuid(),
                    'title' => 'English Pronunciation Mastery',
                    'start_time' => now()->addDays(2)->setTime(10, 0), // Day after tomorrow at 10 AM
                    'end_time' => now()->addDays(2)->setTime(12, 0),   // Ends at 12 PM
                    'description' => 'Perfect your English pronunciation with tips from native speakers.',
                    'user_id' => $instructor->id,
                    'status' => 'live',
                    'max_participants' => 100,
                    'stream_url' => 'https://example.com/stream/english-pronunciation-mastery',
                ],
                [
                    'slug' => Str::uuid(),
                    'title' => 'English for Business Communication',
                    'start_time' => now()->addDays(3)->setTime(9, 0), 
                    'end_time' => now()->addDays(3)->setTime(11, 0),  
                    'description' => 'Learn how to communicate professionally in English for business settings.',
                    'user_id' => $instructor->id,
                    'status' => 'live',
                    'max_participants' => 75,
                    'stream_url' => 'https://example.com/stream/english-for-business-communication',
                ],
            ];

            
            foreach ($sessions as $session) {
                LiveSession::create($session);
            }
        } else {
            \Log::error('No instructor found to assign to the sessions.');
        }
    }
}
