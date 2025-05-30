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
        $fakeFeedbacks = array(
            "This product works great for my needs!",
            "I'm really satisfied with the quality.",
            "The service could be improved slightly.",
            "Excellent value for the price paid.",
            "Not exactly what I expected but still good.",
            "Fast delivery and good packaging.",
            "The instructions could be clearer.",
            "Perfect fit for what I was looking for.",
            "Customer support was very helpful.",
            "Would definitely recommend to others."
        );

        foreach ($fakeFeedbacks as $index) {
            FeedBack::create([
                'rate'           => rand(2, 10) / 2,
                'comment'        => $index,
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
