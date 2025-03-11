<?php

namespace Database\Seeders\Course;

use App\Models\Course\Course;
use App\Models\Course\CourseModule;
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
            $courseModule = CourseModule::first();

            $courseContents = [
                // [
                //     'slug' => Str::uuid(),
                //     'course_id' => $course->id,
                //     'title' => 'English Speaking: Introduction',
                //     'description' => 'This course covers basic English speaking skills with real-life conversations and exercises.',
                //     'content_type' => VIDEO,
                //     'hour' => '2:00',
                //     'status' => DRAFT,
                //     'sequence' => 1,
                //     'course_module_id' => $courseModule->id,
                //     'thumbnail_url' => '/course/course-1.jpg',
                // ],
                // [
                //     'slug' => Str::uuid(),
                //     'course_id' => $course->id,
                //     'title' => 'Advanced English Grammar',
                //     'description' => 'Learn complex sentence structures, tenses, and advanced grammatical rules.',
                //     'content_type' => PDF,
                //     'hour' => '1:30',
                //     'status' => DRAFT,
                //     'sequence' => 2,
                //     'course_module_id' => $courseModule->id,
                //     'thumbnail_url' => '/course/course-2.jpg',
                // ],
                // [
                //     'slug' => Str::uuid(),
                //     'course_id' => $course->id,
                //     'title' => 'Mastering Pronunciation',
                //     'description' => 'Focus on phonetics, stress patterns, and common pronunciation errors.',
                //     'content_type' => VIDEO,
                //     'hour' => '1:00',
                //     'status' => DRAFT,
                //     'sequence' => 3,
                //     'course_module_id' => $courseModule->id,
                //     'thumbnail_url' => '/course/course-3.webp',
                // ],
                [
                    'slug' => Str::uuid(),
                    'course_id' => $course->id,
                    'title' => 'English Speaking: Introduction',
                    'description' => 'This course covers basic English speaking skills with real-life conversations and exercises.',
                    'content_type' => VIDEO,
                    'hour' => '2:00',
                    'status' => DRAFT,
                    'sequence' => 1,
                    'course_module_id' => $courseModule->id,
                    'thumbnail_url' => '/course/course-1.jpg',
                ],
                [
                    'slug' => Str::uuid(),
                    'course_id' => $course->id,
                    'title' => 'Advanced English Grammar',
                    'description' => 'Learn complex sentence structures, tenses, and advanced grammatical rules.',
                    'content_type' => PDF,
                    'hour' => '1:30',
                    'status' => DRAFT,
                    'sequence' => 2,
                    'course_module_id' => $courseModule->id,
                    'thumbnail_url' => '/course/course-2.jpg',
                ],
                [
                    'slug' => Str::uuid(),
                    'course_id' => $course->id,
                    'title' => 'Mastering Pronunciation',
                    'description' => 'Focus on phonetics, stress patterns, and common pronunciation errors.',
                    'content_type' => VIDEO,
                    'hour' => '1:00',
                    'status' => DRAFT,
                    'sequence' => 3,
                    'course_module_id' => $courseModule->id,
                    'thumbnail_url' => '/course/course-3.webp',
                ],
                [
                    'slug' => Str::uuid(),
                    'course_id' => $course->id,
                    'title' => 'Listening Comprehension Skills',
                    'description' => 'Train your ears with real conversations and practice listening to native speakers.',
                    'content_type' => VIDEO,
                    'hour' => '1:45',
                    'status' => DRAFT,
                    'sequence' => 4,
                    'course_module_id' => $courseModule->id,
                    'thumbnail_url' => '/course/coursefgg-4.jpeg',
                ],
                [
                    'slug' => Str::uuid(),
                    'course_id' => $course->id,
                    'title' => 'Writing Skills: Essays & Reports',
                    'description' => 'Learn how to structure essays, reports, and formal writing in English.',
                    'content_type' => PDF,
                    'hour' => '2:15',
                    'status' => DRAFT,
                    'sequence' => 5,
                    'course_module_id' => $courseModule->id,
                    'thumbnail_url' => '/course/course-5.jpg',
                ],
                [
                    'slug' => Str::uuid(),
                    'course_id' => $course->id,
                    'title' => 'Conversational French for Beginners',
                    'description' => 'Learn essential phrases and conversations for daily interactions in French.',
                    'content_type' => VIDEO,
                    'hour' => '1:30',
                    'status' => DRAFT,
                    'sequence' => 6,
                    'course_module_id' => $courseModule->id,
                    'thumbnail_url' => '/course/course-6.jpg',
                ],
                [
                    'slug' => Str::uuid(),
                    'course_id' => $course->id,
                    'title' => 'Spanish Vocabulary Essentials',
                    'description' => 'Expand your Spanish vocabulary with commonly used words and expressions.',
                    'content_type' => PDF,
                    'hour' => '1:00',
                    'status' => DRAFT,
                    'sequence' => 7,
                    'course_module_id' => $courseModule->id,
                    'thumbnail_url' => '/course/course-7.jpeg',
                ],
                [
                    'slug' => Str::uuid(),
                    'course_id' => $course->id,
                    'title' => 'Chinese Characters & Writing Basics',
                    'description' => 'Learn the fundamentals of reading and writing Chinese characters.',
                    'content_type' => PDF,
                    'hour' => '2:30',
                    'status' => DRAFT,
                    'sequence' => 8,
                    'course_module_id' => $courseModule->id,
                    'thumbnail_url' => '/course/course-8.webp',
                ]
            ];


            foreach($courseContents as $courseContent) {
                $user->courseContents()->create($courseContent);
            }
        } catch (Exception $e) {
            dd($e);
        }
    }
}
