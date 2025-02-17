<?php

namespace Database\Seeders\Quize;

use Exception;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Course\Course;
use App\Models\Quize\QMetaData;
use Illuminate\Database\Seeder;
use App\Models\Course\CourseContent;

class QMetaDataSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $user = User::first();
            $course = Course::first();
            $courseContent = CourseContent::first();

            $qMetaDataRecords = [
                [
                    'slug' => Str::uuid(),
                    'question_type' => 'multiple_choice',
                    'instraction' => 'Select the correct answer from the given options.',
                    'title' => 'General Knowledge Question',
                    'user_id' => $user->id,
                    'course_content_id' => $courseContent->id,
                    'course_id' => $course->id,
                ],
                [
                    'slug' => Str::uuid(),
                    'question_type' => 'true_false',
                    'instraction' => 'Mark the statement as true or false.',
                    'title' => 'Basic Science Fact',
                    'user_id' => $user->id,
                    'course_content_id' => $courseContent->id,
                    'course_id' => $course->id,
                ],
                [
                    'slug' => Str::uuid(),
                    'question_type' => 'short_answer',
                    'instraction' => 'Provide a short and concise answer.',
                    'title' => 'Mathematics Problem',
                    'user_id' => $user->id,
                    'course_content_id' => $courseContent->id,
                    'course_id' => $course->id,
                ]
            ];

            foreach ($qMetaDataRecords as $qMetaData) {
                QMetaData::create($qMetaData);
            }
        } catch (Exception $e) {
            dd($e);
        }
    }
}
