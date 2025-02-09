<?php

namespace Database\Seeders;

use Database\Seeders\Book\BookSeeder;
use Database\Seeders\Book\OrderBookSeeder;
use Database\Seeders\Course\CourseContentSeeder;
use Database\Seeders\Course\CourseModuleSeeder;
use Database\Seeders\Course\CourseSeeder;
use Database\Seeders\Role\SystemAdminSeeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\Role\StudentSeeder;
use Database\Seeders\Role\InstructorSeeder;
use Database\Seeders\Live\LiveSessionSeeder;
use Database\Seeders\Live\ParticipantSeeder;
use Database\Seeders\Live\LiveResourceSeeder;
use Database\Seeders\Live\VirtualClassEnrollmentSeeder;

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
            CourseModuleSeeder::class,
            CourseContentSeeder::class,

            BookSeeder::class,
            OrderBookSeeder::class,

            LiveSessionSeeder::class,
            LiveResourceSeeder::class,
            ParticipantSeeder::class,
        ]);
    }
}
