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
                [
                    'slug' => Str::uuid(),
                    'course_name' => 'English Grammar Mastery',
                    'overview' => 'Covers fundamental to advanced grammar rules, sentence structure, and usage.Grammar is the system of
                    rules that govern the structure of sentences in a language. It includes various components such as parts of speech, 
                    sentence structure, tenses, punctuation, and more. Here’s an overview of the key elements of English grammar:',
                    'skill_level' => 1,
                    'price' => 500,
                    'discount' => 200,
                    'credit_hour' => 12,
                    'language' => 'English',
                    'thumbnail_url' => '/images/course-1.jpg',
                    'status' => 'published',
                    'intro_video' => '',
                ],
                [
                    'slug' => Str::uuid(),
                    'course_name' => 'Conversational English',
                    'overview' => 'Learn practical speaking skills for everyday communication and business settings.Grammar
                    is the system of rules that govern the structure of sentences in a language. It includes various components 
                    such as parts of speech, sentence structure, tenses, punctuation, 
                    and more. Here’s an overview of the key elements of English grammar:',
                    'skill_level' => 2, 
                    'price' => 600,
                    'discount' => 150,
                    'credit_hour' => 15,
                    'language' => 'English',
                    'thumbnail_url' => '/images/course-2.jpg',
                    'status' => 'published',
                    'intro_video' => '',
                ],
                [
                    'slug' => Str::uuid(),
                    'course_name' => 'Advanced English Writing',
                    'overview' => 'Develop academic and business writing skills, including essays, reports, and emails.
                    Grammar is the system of rules that govern the structure of sentences in a language. It includes various 
                    components such as parts of speech, sentence structure,
                    tenses, punctuation, and more. Here’s an overview of the key elements of English grammar:',
                    'skill_level' => 3, 
                    'price' => 700,
                    'discount' => 100,
                    'credit_hour' => 18,
                    'language' => 'English',
                    'thumbnail_url' => '/images/course-3.webp',
                    'status' => 'published',
                    'intro_video' => '',
                ],
                [
                    'slug' => Str::uuid(),
                    'course_name' => 'Spanish for Beginners',
                    'overview' => 'Learn basic Spanish vocabulary, grammar, and common phrases for conversation.',
                    'skill_level' => 1,
                    'price' => 450,
                    'discount' => 100,
                    'credit_hour' => 10,
                    'language' => 'English',
                    'thumbnail_url' => '/images/course-4.jpeg',
                    'status' => 'published',
                    'intro_video' => '',
                ],
                [
                    'slug' => Str::uuid(),
                    'course_name' => 'French Pronunciation & Speaking',
                    'overview' => 'Improve French pronunciation and develop fluent speaking skills through practice.',
                    'skill_level' => 2, 
                    'price' => 550,
                    'discount' => 120,
                    'credit_hour' => 14,
                    'language' => 'English',
                    'thumbnail_url' => '/images/course-5.jpg',
                    'status' => 'published',
                    'intro_video' => '',
                ],
                [
                    'slug' => Str::uuid(),
                    'course_name' => 'Mandarin Chinese Essentials',
                    'overview' => 'Learn basic Mandarin Chinese tones, pronunciation, and writing characters.',
                    'skill_level' => 1,
                    'price' => 650,
                    'discount' => 200,
                    'credit_hour' => 16,
                    'language' => 'English',
                    'thumbnail_url' => '/images/course-6.jpg',
                    'status' => 'published',
                    'intro_video' => '',
                ],
                [
                    'slug' => Str::uuid(),
                    'course_name' => 'German A1 - Foundations',
                    'overview' => 'Start learning German with essential grammar, vocabulary, and sentence structure.',
                    'skill_level' => 1,
                    'price' => 500,
                    'discount' => 150,
                    'credit_hour' => 12,
                    'language' => 'English',
                    'thumbnail_url' => '/images/course-7.jpeg',
                    'status' => 'published',
                    'intro_video' => '',
                ],
                [
                    'slug' => Str::uuid(),
                    'course_name' => 'Japanese Kanji & Writing',
                    'overview' => 'Understand the basics of reading, writing, and using Kanji characters in Japanese.',
                    'skill_level' => 3, 
                    'price' => 800,
                    'discount' => 200,
                    'credit_hour' => 20,
                    'language' => 'English',
                    'thumbnail_url' => '/images/course-8.webp',
                    'status' => 'published',
                    'intro_video' => '',
                ]
            ];

            foreach($courses as $course){
                $user->courses()->create($course);
            }


        } catch(Exception $e) {
            dd($e);
        }
    }
}
