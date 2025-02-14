<?php

namespace Database\Seeders\Quize;

use App\Models\User;
use App\Models\Quiz\Quize;
use Illuminate\Support\Str;
use App\Models\Quize\QMetaData;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('No users found! Please run UserSeeder first.');
            return;
        }

        foreach ($users as $user) {
            $qMetaData = QMetaData::where('user_id', $user->id)->latest()->first();

            if (!$qMetaData) {
                $qMetaData = QMetaData::create([
                    'user_id' => $user->id,
                    'metadata' => json_encode(['example' => 'meta data'])
                ]);
            }

            Quiz::create([
                'slug' => Str::uuid(),
                'question_type' => 'multiple_choice',
                'question' => 'Which word is a synonym for "happy"?',
                'choice' => json_encode(['Sad', 'Excited', 'Joyful', 'Angry']),
                'answer' => json_encode(['Joyful']),
                'q_meta_data_id' => $qMetaData->id,
                'user_id' => $user->id,
            ]);

            Quiz::create([
                'slug' => Str::uuid(),
                'question_type' => 'true_false',
                'question' => 'The past tense of "go" is "goed".',
                'choice' => json_encode(['True', 'False']),
                'answer' => json_encode(['False']),
                'q_meta_data_id' => $qMetaData->id,
                'user_id' => $user->id,
            ]);

            Quiz::create([
                'slug' => Str::uuid(),
                'question_type' => 'multiple_choice',
                'question' => 'Which sentence is grammatically correct?',
                'choice' => json_encode([
                    'She don’t like apples.',
                    'She doesn’t like apples.',
                    'She not like apples.',
                    'She no like apples.'
                ]),
                'answer' => json_encode(['She doesn’t like apples.']),
                'q_meta_data_id' => $qMetaData->id,
                'user_id' => $user->id,
            ]);

            Quiz::create([
                'slug' => Str::uuid(),
                'question_type' => 'fill_in_the_blank',
                'question' => 'Complete the sentence: "I ____ to school every day."',
                'choice' => json_encode(['go', 'went', 'gone', 'going']),
                'answer' => json_encode(['go']),
                'q_meta_data_id' => $qMetaData->id,
                'user_id' => $user->id,
            ]);

            Quiz::create([
                'slug' => Str::uuid(),
                'question_type' => 'true_false',
                'question' => 'The word "receive" is spelled correctly.',
                'choice' => json_encode(['True', 'False']),
                'answer' => json_encode(['True']),
                'q_meta_data_id' => $qMetaData->id,
                'user_id' => $user->id,
            ]);
        }
    }
}
