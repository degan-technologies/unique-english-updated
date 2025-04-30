<?php

namespace App\Http\Controllers\Schedule;

use App\Http\Controllers\Controller;
use App\Http\Resources\Schedule\ScheduleResource;
use App\Models\Schedule\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\LangService; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller {

    protected $langService;

    public function __construct(LangService $langService) {
        $this->langService = $langService;
    }

    public function index() { 
        $schedules = Schedule::all();
        return response()->json(['data' => ScheduleResource::collection($schedules)]);
    }

    public function store(Request $request) {

        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$user) {
            return;
        }

        $validationRules = [
            'day' => 'required',
            'time' => 'required',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('schedules'));

        if ($validator->fails()) {
            return response()->json([
                'message' => $this->langService->getLang('validation_failed'),
                'errors' => $validator->errors(),
            ], 422);
        }
 
        $schedule = $user->schedules()->create([
            'day' => $request->day,
            'time' => "10:00:00",
            'schedule_time' => $request->time,
        ]);

        return response()->json([
            'message' => $this->langService->getLang('schedule_created'),
            'data' => new ScheduleResource($schedule),
        ]);
    }

    public function show($id) {
        try { 
            $schedule = Schedule::findOrFail($id);
            return response()->json(['data' => new ScheduleResource($schedule)]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $this->langService->getLang('schedules.not_found')], 404);
        }
    }

    public function update(Request $request, $id) {
        try {
            // Removed the user_id filter
            $schedule = Schedule::query()
                ->where('user_id', Auth::id())
                ->where('schedule_time',  "!=", $request->time)
                ->where('id', $id)
                ->firstOrFail();

            if (!$schedule) {
                return response()->json([
                    'message' => $this->langService->getLang('schedules_not_found')
                ], 404);
            }

            $validationRules = [
                'day' => 'required|string',
                'time' => 'required',
            ];

            $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('schedules'));

            if ($validator->fails()) {
                return response()->json([
                    'message' => $this->langService->getLang('validation_failed'),
                    'errors' => $validator->errors(),
                ], 422);
            } 
 
            $schedule->update([
                'day' => $request->day,
                'schedule_time' => $request->time,
            ]);

            return response()->json([
                'message' => $this->langService->getLang('schedule_updated'),
                'data' => new ScheduleResource($schedule),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $this->langService->getLang('schedules_not_found')], 404);
        }
    }

    public function destroy($id) {
        try {
            // Removed the user_id filter
            $schedule = Schedule::findOrFail($id);
            $schedule->delete();

            return response()->json(['message' => $this->langService->getLang('schedule_deleted')]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $this->langService->getLang('schedules_not_found')], 404);
        }
    }

    public function getMySchedules() {
        $user = Auth::user();

        $schedules = Schedule::query()
            ->where(function($query) use($user) {
                $query->orWhere('user_id', $user->id)
                    ->orWhere('user_id', $user->id);
            })
            ->where('status', '!=', COMPLETED)
            ->get();        

        if(!$schedules) {
            return response()->json([
                'data' => 'no Schedules found'
            ]);
        }

        return response()->json([
            'data' => ScheduleResource::collection($schedules)
        ]);
    }
}