<?php

namespace Database\Seeders\Quiz;

use App\Models\User;
use App\Models\Quiz\Quiz;
use Illuminate\Support\Str;
use App\Models\Quiz\QMetaData;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    public function run()
    {
        $user = User::first();
 
        $qMetaData = QMetaData::first();

        
            Quiz::create([
                'slug' => Str::uuid(),
                'question_type' => 'multiple_choice',
                'question' => 'Which word is a synonym for "happy"?',
                'choice' => ['Sad', 'Excited', 'Joyful', 'Angry'],  
                'answer' => ['Joyful'],  
                'q_meta_data_id' => $qMetaData->id,  
                'user_id' => $user->id,
            ]);
            
        $this->command->info('QuizSeeder executed successfully!');
    }
}
