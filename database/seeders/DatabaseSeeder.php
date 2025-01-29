<?php

namespace Database\Seeders;


use App\Models\User;
use Database\Seeders\Role\SecondaryAdminSeeder;
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

            SecondaryAdminSeeder::class,
            InstructorSeeder::class,
            StudentSeeder::class,
            LiveResourceSeeder::class,
            LiveSessionSeeder::class,
            ParticipantSeeder::class,
            VirtualClassEnrollmentSeeder::class,
        ]);
    }
}
