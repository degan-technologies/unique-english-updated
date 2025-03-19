<?php

namespace Database\Seeders;


use Database\Seeders\Message\MessageSeeder;
use Illuminate\Database\Seeder;
use Database\Seeders\Book\BookSeeder;
use Database\Seeders\Book\OrderBookSeeder;
use Database\Seeders\Comment\FeedBackSeeder;
use Database\Seeders\Course\CourseContentSeeder;
use Database\Seeders\Course\CourseModuleSeeder;
use Database\Seeders\Course\CourseSeeder;
use Database\Seeders\Role\SystemAdminSeeder;
use Database\Seeders\Role\StudentSeeder;
use Database\Seeders\Role\InstructorSeeder;
use Database\Seeders\Live\LiveSessionSeeder;
use Database\Seeders\Live\ParticipantSeeder;
use Database\Seeders\Live\LiveResourceSeeder;
use Database\Seeders\Live\VirtualClassEnrollmentSeeder;

use Database\Seeders\Quiz\QuizSeeder;
use Database\Seeders\Quiz\ResultSeeder;
use Database\Seeders\Quiz\QMetaDataSeeder;
use Database\Seeders\Quiz\QASectionSeeder;
use Database\Seeders\Schedule\ScheduleSeeder;
use Database\Seeders\System\PlatformComissionSeeder;
use Database\Seeders\Test\TestSeeder;

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
            CourseSeeder::class,
            CourseModuleSeeder::class,
            CourseContentSeeder::class,

            BookSeeder::class,
            OrderBookSeeder::class,

            LiveSessionSeeder::class,
            LiveResourceSeeder::class,
            ParticipantSeeder::class,

            FeedBackSeeder::class,
            VirtualClassEnrollmentSeeder::class,

            QMetaDataSeeder::class,
            QuizSeeder::class,
            ResultSeeder::class,
            TestSeeder::class,
            ScheduleSeeder::class,

            QASectionSeeder::class,

            PlatformComissionSeeder::class
        ]);
    }
}
