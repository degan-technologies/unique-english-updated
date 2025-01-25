<?php

namespace Database\Seeders\Course;

use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        try{
            $user = User::first();
            $courses = [
                'slug' => Str::uuid(),
                'course_name' => 'English grammer',
                'overview' => 'this course has three parts',
                'skill_level' => 1,
                'price' => 500,
                'discount' => 200,
                'credit_hour' => 12
            ];

            $user->courses()->create($courses);

        } catch(Exception $e) {
            dd($e);
        }
    }
}
