<?php

namespace Database\Seeders\Course;

use App\Models\Course\Course;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseContentSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $user = User::first();
            $course = Course::first();

            $courseContent = [
                'slug' => Str::uuid(),
                'course_id' => $course->id,
                'title' => 'english speaking introduction',
                'description' => 'this course has three parts and this introduction is to improve speaking"',
                'content_type' => VIDEO,
                'hour' => '2:00',
                'status' => DRAFT,
                'note' => 'speaking can improve throught communicating with others'
            ];

            $user->courseContents()->create($courseContent);
        } catch (Exception $e) {
            dd($e);
        }
    }
}
