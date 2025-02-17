<?php

namespace Database\Seeders\Quize;

use App\Models\User;
use App\Models\Course\Course;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Models\Quize\QASection;

class QASectionSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        foreach ($users as $user) {
            $course = $user->courses()->latest()->first();

            if ($course) {
                QASection::create([
                    'question' => 'What is the difference between "their", "there", and "they\'re"?',
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                ]);

                QASection::create([
                    'question' => 'Give an example of a sentence using the past perfect tense.',
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                ]);

                QASection::create([
                    'question' => 'How do you form the comparative and superlative of adjectives?',
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                ]);

                QASection::create([
                    'question' => 'What are the different types of conditional sentences in English?',
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                ]);

                QASection::create([
                    'question' => 'Explain the difference between active and passive voice with examples.',
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                ]);
            }
        }
    }
}
