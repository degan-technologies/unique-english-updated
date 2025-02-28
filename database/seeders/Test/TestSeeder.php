<?php

namespace Database\Seeders\Test;

use App\Models\Test\Test;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\DB;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tests')->insert([
            [
                 'slug' => Str::uuid(),
                'question' => 'What is the capital of France?',
                'choices' => json_encode(['Paris', 'Berlin', 'London', 'Rome']),
                'answer' => json_encode(['Paris']),
                'user_id' => 1,
                'score' => 0,
                'level' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => Str::uuid(),
                'question' => 'Which one is a fruit?',
                'choices' => json_encode(['Carrot', 'Tomato', 'Potato', 'Cabbage']),
                'answer' => json_encode(['Tomato']),
                'user_id' => 1,
                'score' => 0,
                'level' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
