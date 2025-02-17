<?php

namespace Database\Seeders\Quize;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Quize\Result;
use App\Models\Quize\Quize;
use Illuminate\Support\Str;

class ResultSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('No users found! Please run UserSeeder first.');
            return;
        }

        foreach ($users as $user) {
            $quiz = Quiz::where('user_id', $user->id)->latest()->first();

            if (!$quiz) {
                $this->command->info("No quiz found for user {$user->id}. Skipping...");
                continue;
            }

            Result::create([
                'slug' => Str::uuid(),
                'result' => rand(50, 100), 
                'user_id' => $user->id,
                'quize_id' => $quiz->id,
            ]);
        }
    }
}
