<?php

namespace Database\Seeders\Comment;

use Illuminate\Database\Seeder;
use App\Models\Comment\FeedBack;
use App\Models\User;
use App\Models\Course\Course;

class FeedBackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $courses = Course::all();

        if ($users->isEmpty() || $courses->isEmpty()) {
            $this->command->warn("Skipping feedback seeding: Not enough users or courses.");
            return;
        }

        foreach (range(1, 10) as $index) {
            FeedBack::create([
                // Generate a rating between 1 and 5 in half-star increments (e.g., 1, 1.5, 2, ..., 5)
                'rate'           => rand(2, 10) / 2,
                'comment'        => fake()->sentence(),
                'user_id'        => $users->random()->id,
                'instractor_id'  => $users->random()->id,
                'course_id'      => $courses->random()->id,
                // Optionally seed feedback actions
                'likes'          => rand(0, 5),
                'dislikes'       => rand(0, 2),
                'reports'        => 0,
            ]);
        }
    }
}
