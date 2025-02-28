<?php
namespace Database\Seeders\Plan;

use Illuminate\Database\Seeder;
use App\Models\Plan\Plan;

class PlanSeeder extends Seeder {
    public function run(): void {
        Plan::insert([
            ['name' => 'Join Monthly', 'price' => 12000, 'duration' => 1],
            ['name' => 'Join 3 Months', 'price' => 30000, 'duration' => 3],
            ['name' => 'Join 6 Months', 'price' => 56000, 'duration' => 6],
        ]);
    }
}
