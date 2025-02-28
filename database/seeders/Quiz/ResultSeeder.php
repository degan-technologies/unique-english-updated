<?php

namespace Database\Seeders\Quiz;

use App\Models\Quiz\QMetaData;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Quiz\Result;
use App\Models\Quiz\Quiz;
use Illuminate\Support\Str;

class ResultSeeder extends Seeder
{
    public function run()
    {
        $user = User::first();
        $qMetaData = QMetaData::first();  

         Result::create([
                'slug' => Str::uuid(),
                'result' => rand(50, 100), 
                'user_id' => $user->id,
                'q_meta_data_id' => $qMetaData->id,
            ]);
    }
}
