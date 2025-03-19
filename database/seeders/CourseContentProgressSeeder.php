<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course\CourseContentProgress;

class CourseContentProgressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        
        CourseContentProgress::updateOrCreate(
            ['user_id' => 1, 'course_content_id' => 1],
            ['progress' => 'completed']
        );
        CourseContentProgress::updateOrCreate(
            ['user_id' => 1, 'course_content_id' => 2],
            ['progress' => 'completed'] 
        );
        CourseContentProgress::updateOrCreate(
            ['user_id' => 1, 'course_content_id' => 3],
            ['progress' => 'completed']
        );


        CourseContentProgress::updateOrCreate(
            ['user_id' => 2, 'course_content_id' => 1],
            ['progress' => 'completed']
        );
        CourseContentProgress::updateOrCreate(
            ['user_id' => 2, 'course_content_id' => 2],
            ['progress' => 'completed']
        );
        CourseContentProgress::updateOrCreate(
            ['user_id' => 2, 'course_content_id' => 3],
            ['progress' => 'completed']
        );

        CourseContentProgress::updateOrCreate(
            ['user_id' => 3, 'course_content_id' => 1],
            values: ['progress' => '50']  
        );
        CourseContentProgress::updateOrCreate(
            ['user_id' => 3, 'course_content_id' => 2],
            ['progress' => '20']  
        );
        CourseContentProgress::updateOrCreate(
            ['user_id' => 3, 'course_content_id' => 3],
            ['progress' => '0']   
        );
        // --- COURSE 2 ---
        CourseContentProgress::updateOrCreate(
            ['user_id' => 1, 'course_content_id' => 4],
            ['progress' => 'completed']
        );
        CourseContentProgress::updateOrCreate(
            ['user_id' => 1, 'course_content_id' => 5],
            ['progress' => 'completed']
        );

        // User 2: Completed content 5 but only partially watched content 4.
        CourseContentProgress::updateOrCreate(
            ['user_id' => 2, 'course_content_id' => 4],
            ['progress' => 'completed'] // partially watched
        );
        CourseContentProgress::updateOrCreate(
            ['user_id' => 2, 'course_content_id' => 5],
            ['progress' => 'completed']
        );
    }
}
