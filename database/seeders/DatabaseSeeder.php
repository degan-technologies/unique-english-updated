<?php

namespace Database\Seeders;

use Database\Seeders\Course\courseSeeder;
use Database\Seeders\Role\InstructorSeeder;
use Database\Seeders\Role\StudentSeeder;
use Database\Seeders\Role\SystemAdminSeeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     */
    public function run(): void {
        $this->call([
            SystemAdminSeeder::class,
            InstructorSeeder::class,
            StudentSeeder::class,

            CourseSeeder::class,
        ]);
    }
}
