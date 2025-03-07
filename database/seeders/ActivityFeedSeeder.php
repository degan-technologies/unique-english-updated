<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ActivityFeed;
use App\Models\User;
use App\Models\Course\Course;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ActivityFeedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $user = Auth::user();
        $course = Course::first();

        if (!$user) {
            $this->command->warn("No authenticated user found! ");
            $user = User::first(); 
        }

        if (!$user) {
            $this->command->error("No users exist in the database!");
            return;
        }
        if (!$course) {
            $this->command->warn("No courses found! ");
            return;
        }


        // Seed activity feed for authenticated user
        ActivityFeed::insert([
            [
                'user_id' => $user->id,
            'course_id' => null,  // No course for login activity
            'type' => 'login',
            'description' => "{$user->name} logged in.",
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => $user->id,
                'course_id' => $course->id, // Ensure course_id is correctly assigned
                'type' => 'enroll',
                'description' => "{$user->name} enrolled in {$course->title}.",
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ],
            [
                'user_id' => $user->id,
                'course_id' => $course->id, // Ensure course_id is correctly assigned
                'type' => 'complete',
                'description' => "{$user->name} completed {$course->title}.",
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ],
        ]);

        $this->command->info("Activity Feed seeded successfully for user: {$user->name} (ID: {$user->id})!");
    }
}
