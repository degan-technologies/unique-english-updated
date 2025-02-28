<?php

namespace App\Http\Controllers\Schedule;

use App\Http\Controllers\Controller;
use App\Http\Resources\Schedule\ScheduleResource;
use App\Models\Schedule\Schedule;
use Illuminate\Http\Request;
use App\Services\LangService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ScheduleController extends Controller {

    protected $langService;

    public function __construct(LangService $langService) {
        $this->langService = $langService;
    }

    public function index() {
        // Fetch all schedules without filtering by user_id
        $schedules = Schedule::all();
        return response()->json(['data' => ScheduleResource::collection($schedules)]);
    }

    public function store(Request $request) {
        $validationRules = [
            'day' => 'required',
            'time' => 'required',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return response()->json([
                'message' => $this->langService->getLang('validation_failed'),
                'errors' => $validator->errors(),
            ], 422);
        }

        // Add the authenticated user's ID to the schedule
        $schedule = Schedule::create([
            'day' => $request->day,
            'time' => $request->time,
            'user_id' => auth()->id(),  // Add user_id here
        ]);

        return response()->json([
            'message' => $this->langService->getLang('schedule_created'),
            'data' => new ScheduleResource($schedule),
        ]);
    }

    public function show($id) {
        try {
            // Removed the user_id filter
            $schedule = Schedule::findOrFail($id);
            return response()->json(['data' => new ScheduleResource($schedule)]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $this->langService->getLang('schedules.not_found')], 404);
        }
    }

    public function update(Request $request, $id) {
        try {
            // Removed the user_id filter
            $schedule = Schedule::findOrFail($id);

            $validationRules = [
                'day' => 'required|string',
                'time' => 'required|date_format:H:i',
            ];

            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $this->langService->getLang('validation_failed'),
                    'errors' => $validator->errors(),
                ], 422);
            }

            if (Schedule::where('day', $request->day)->where('time', $request->time)->where('id', '!=', $id)->exists()) {
                return response()->json(['message' => $this->langService->getLang('schedule_exists')], 409);
            }

            // Add the authenticated user's ID to the schedule during update (if needed)
            $schedule->update(array_merge($request->only(['day', 'time']), ['user_id' => auth()->id()]));

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
}
