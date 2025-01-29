<?php

namespace Database\Seeders\Live;

use App\Models\Live\LiveResource;
use App\Models\User;
use App\Models\Live\LiveSession;
use Illuminate\Database\Seeder;

class LiveResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void{
        
        $user = User::first();  
        $liveSession = LiveSession::first();  

        LiveResource::create([
            'resource_name' => 'Introduction to tenses',
            'slug' => 'intro-to-tenses',
            'resurce_url' => 'https://example.com/intro-to-tenses',
            'live_session_id' => $liveSession->id,
            'user_id' => $user->id,
        ]);
        
        LiveResource::create([
            'resource_name' => 'introduction to adjectives',
            'slug' => 'intro-to-adjectives',
            'resurce_url' => 'https://example.com/intro-to-adjectives',
            'live_session_id' => $liveSession->id,
            'user_id' => $user->id,
        ]);
        LiveResource::create([
            'resource_name' => 'Deep Dive into Neural Networks',
            'slug' => 'deep-dive-neural-networks',
            'resurce_url' => 'https://example.com/deep-dive-neural-networks',
            'live_session_id' => $liveSession->id,
            'user_id' => $user->id,
        ]);
    }
}
