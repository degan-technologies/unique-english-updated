<?php

namespace Database\Seeders\Schedule;

use Illuminate\Database\Seeder;
use App\Models\Schedule\Schedule;
use App\Models\User;
use App\Services\LangService;
use Illuminate\Support\Str;

class ScheduleSeeder extends Seeder
{

     protected $langService;

    public function __construct(LangService $langService) {
        $this->langService = $langService;
    }
    public function run()
    { 
        $user = User::first();

         
        $schedules = [
            ['day' => 'Monday',    'schedule_time' => '09:00 AM', 'room_name' => Str::uuid()],
            ['day' => 'Monday',    'schedule_time' => '14:00 AM', 'room_name' => Str::uuid()], 
        ];
 
        foreach ($schedules as $data) {
            Schedule::create([
                'user_id' => $user->id,
                'day'     => $data['day'],
                'schedule_time'    => $data['schedule_time'], 
                'room_name'    => $data['room_name'], 
            ]);
        }

        $this->command->info('Schedule seeder executed successfully.');
    }
}
