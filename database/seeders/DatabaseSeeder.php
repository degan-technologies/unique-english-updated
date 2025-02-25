<?php

namespace Database\Seeders;


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
use Database\Seeders\Message\MessageSeeder;
use Database\Seeders\Quize\QuizSeeder;
use Database\Seeders\Quize\ResultSeeder;
use Database\Seeders\Quize\QMetaDataSeeder;
use Database\Seeders\Quize\QASectionSeeder;


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

            FeedBackSeeder::class,
            VirtualClassEnrollmentSeeder::class,

            QuizSeeder::class,
            QMetaDataSeeder::class,
            QASectionSeeder::class,
            ResultSeeder::class,

            MessageSeeder::class,
        ]);
    }
}
