<?php

namespace Database\Seeders\Live;

use App\Models\User;
use App\Models\Live\LiveSession;
use App\Models\Live\Participant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ParticipantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::whereSystemAdminOrInstructor()->first();
        $liveSession = LiveSession::where('user_id', $user->id)->latest()->first();

        if (!$liveSession) {
            $liveSession = LiveSession::create([
                'slug' => Str::uuid(),
                'status' => 'live',
                'title' => 'English Tutorial Live Session',
                'start_time' => now()->addHours(2),
                'end_time' => now()->addHours(3),
                'description' => 'A live session for learning English.',
                'instructor_id' => $user->id,
                'max_participants' => 50,
                'stream_url' => 'http://example.com/stream',
            ]);
        }

        $participants = [
            ['joined_at' => now()->addMinutes(5), 'status' => 'registered'],
            ['joined_at' => now()->addMinutes(10), 'status' => 'joined'],
            ['joined_at' => now()->addMinutes(15), 'status' => 'left'],
            ['joined_at' => now()->addMinutes(20), 'status' => 'joined'],
            ['joined_at' => now()->addMinutes(30), 'status' => 'left'],
        ];

        foreach ($participants as $data) {
            Participant::create([
                'slug' => Str::uuid(),
                'joined_at' => $data['joined_at'],
                'status' => $data['status'],
                'live_session_id' => $liveSession->id,
                'user_id' => $user->id,  
            ]);
        }

        $this->command->info('Participants table seeded!');
    }
}
