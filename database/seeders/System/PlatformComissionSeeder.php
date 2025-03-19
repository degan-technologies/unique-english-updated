<?php

namespace Database\Seeders\System;

use App\Models\User;
use Illuminate\Database\Seeder;

class PlatformComissionSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {

        $user = User::query()
            ->has('systemAdmin')
            ->first();

        $platformComission = $user->platformComissions()->create([
            'fees' => 0.00,
            'created_at' => now(),
        ]);

        $platformComission->platformComissionHistories()->create([
            'fees' => $platformComission->fees,
            'user_id' => $platformComission->user_id,
            'created_at' => now(),
        ]);
    }
}
