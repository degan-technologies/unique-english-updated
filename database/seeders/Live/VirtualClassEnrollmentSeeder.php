<?php

namespace Database\Seeders\Live;

use Illuminate\Database\Seeder;
use App\Models\VirtualClassEnrollment;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;

class VirtualClassEnrollmentSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        foreach ($users as $user) {
            for ($i = 0; $i < 5; $i++) {
                VirtualClassEnrollment::create([
                    'slug' => Str::uuid(),
                    'user_id' => $user->id,
                    'instructor_id' => $user->id,  
                    'enrolled_at' => Carbon::now()->subDays(rand(1, 30))->toDateString(),  
                    'price_plan' => 'Basic Plan ' . rand(1, 3),  
                    'remaining_date' => rand(5, 100),  
                    'end_date' => Carbon::now()->addDays(rand(10, 60))->toDateString(),  
                ]);
            }
        }

        $this->command->info('VirtualClassEnrollment records seeded successfully.');
    }
}
