<?php

namespace Database\Seeders\Hero;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HeroSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {

        $user = User::query()
            ->has('systemAdmin')
            ->first();  
            
        $user->hero()->create([
            'title'=>'Learn without limits, Anytime, Anywhere',
            'description'=>'Empower Your future with world-class courses, expert instructors, and flexible learning experiance tailored to your needs.',
            'logo'=>'/images/logo.png',
            'app_name'=>'Unique English',
            'banner'=>'/images/Learning.jpg',
            'background_image'=>'/images/here-back.JPG',
        ]);
    }
}
