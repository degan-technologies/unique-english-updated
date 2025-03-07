<?php

namespace Database\Seeders\Course;

use App\Models\Course\Course;
use App\Models\User;
use Illuminate\Support\Str;

use Illuminate\Database\Seeder;

class CourseModuleSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $user = User::first();
        $course = Course::first();

        $courseModules = [
            [
                'slug' => Str::uuid(),

                'title' => 'Module 1: Introduction to Filmmaking',
                'sequence' => 1,
                'description' => ' Introduction to Filmmaking Camera Techniques Audio and Sound Design Video Editing with Adobe Premiere Pro',
                'course_id' => $course->id,
            ],
            [
                'slug' => Str::uuid(),

                'title' => 'Module 2: Camera Techniques',
                'sequence' => 2,
                'description' => 'Introduction to Filmmaking Camera Techniques Audio and Sound Design Video Editing with Adobe Premiere Pro',
                'course_id' => $course->id,
            ],
            [
                'slug' => Str::uuid(),

                'title' => 'Module 3: Audio and Sound Design',
                'sequence' => 3,
                'description' => 'Introduction to Filmmaking Camera Techniques Audio and Sound Design Video Editing with Adobe Premiere Pro',
                'course_id' => $course->id,
            ],
            [
                'slug' => Str::uuid(),

                'title' => 'Module 4: Video Editing with Adobe Premiere Pro',
                'sequence' => 4,
                'description' => 'Introduction to Filmmaking Camera Techniques Audio and Sound Design Video Editing with Adobe Premiere Pro',
                'course_id' => $course->id,
            ]
        ];

        foreach($courseModules as $courseModule){
            $user->courseModules()->create($courseModule);
        }
    }
}
