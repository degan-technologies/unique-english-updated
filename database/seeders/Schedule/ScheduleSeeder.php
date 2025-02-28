<?php

namespace Database\Seeders\Schedule;

use Illuminate\Database\Seeder;
use App\Models\Schedule\Schedule;
use App\Models\User;
use App\Services\LangService;

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
            ['day' => 'Monday',    'time' => '09:00'],
            ['day' => 'Monday',    'time' => '14:00'],
            ['day' => 'Tuesday',   'time' => '10:00'],
            ['day' => 'Wednesday', 'time' => '11:00'],
            ['day' => 'Thursday',  'time' => '13:00'],
            ['day' => 'Friday',    'time' => '15:00'],
        ];
 
        foreach ($schedules as $data) {
            Schedule::create([
                'user_id' => $user->id,
                'day'     => $data['day'],
                'time'    => $data['time'], 
            ]);
        }

        $this->command->info('Schedule seeder executed successfully.');
    }
}
