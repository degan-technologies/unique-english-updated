<?php

namespace Database\Seeders\Course;

use App\Models\Course\Course;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnrollmentSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {

        $user = User::first();
        $course = Course::first();

        $enrollments = [
            [
                'slug' => Str::uuid(),
                'enrolled_at' => now(),
                'progress' => 25,
                'course_id' => $course->id, 
            ],
            [
                'slug' => Str::uuid(),
                'enrolled_at' => now(),
                'progress' => 50, 
                'course_id' => $course->id, 
            ],
            [
                'slug' => Str::uuid(),
                'enrolled_at' => now(),
                'progress' => 75, 
                'course_id' => $course->id, 
            ],
            [
                'slug' => Str::uuid(),
                'enrolled_at' => now(),
                'progress' => 10,
                'course_id' => $course->id, 
            ]
        ];

       try{
            DB::beginTransaction();
                foreach ($enrollments as $enrollment) {
                    $user->enrollments()->create($enrollment);
                }
            DB::commit();
       } Catch( Exception $e) {

            DB::rollBack();
            DD($e);
        }
    }
}