<?php

namespace App\Http\Controllers\Schedule;

use App\Http\Controllers\Controller;
use App\Http\Resources\Schedule\ScheduleResource;
use App\Http\Resources\Schedule\TodayScheduleResource;
use App\Models\Live\GroupRoom;
use App\Models\Live\LiveRooms;
use App\Models\Live\PeredicTable;
use App\Models\Schedule\Schedule;
use App\Models\Transaction\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\LangService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ScheduleController extends Controller
{

    protected $langService;

    public function __construct(LangService $langService)
    {
        $this->langService = $langService;
    }

    public function index()
    {
        $schedules = Schedule::all();
        return response()->json(['data' => ScheduleResource::collection($schedules)]);
    }

    public function store(Request $request)
    {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$user) {
            return;
        }

        $liveRoomId = $request->live_room_id ?? null;

        $liveRoom = LiveRooms::query()
            ->where('id', $liveRoomId)
            ->first();

        if (!$liveRoom) {
            return response()->json([
                'message' => $this->langService->getLang('live_room_not_found')
            ], 422);
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

        try {
            DB::beginTransaction();

            $schedule = $user->schedules()->create([
                'day' => $request->day,
                'schedule_time' => $request->time,
                'room_name' => Str::uuid(),
            ]);

            $chedule = PeredicTable::create([
                'schedule_id' => $schedule->id,
                'live_room_id' => $liveRoomId,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $this->langService->getLang('schedule_not_created'),
                'errors' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => $this->langService->getLang('schedule_created'),
            'data' => new ScheduleResource($schedule),
        ]);
    }

    public function show($id)
    {
        try {
            $schedule = Schedule::findOrFail($id);
            return response()->json(['data' => new ScheduleResource($schedule)]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $this->langService->getLang('schedules.not_found')], 404);
        }
    }

    public function update(Request $request, $id) {
        try {

            $liveRoomId = $request->live_room_id ?? null;

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

            $liveRoom = LiveRooms::query()
                ->where('id', $liveRoomId)
                ->first();

            if (!$liveRoom) {
                return response()->json([
                   'message' => $this->langService->getLang('live_room_not_found')
                ], 422);
            }

            $schedule = Schedule::query()
                ->where('user_id', Auth::id())
                ->where('id', $id)
                ->firstOrFail();

            if (!$schedule) {
                return response()->json([
                    'message' => $this->langService->getLang('schedules_not_found')
                ], 404);
            } 
           
            try {
                DB::beginTransaction();

                $schedule->update([
                    'day' => $request->day,
                    'schedule_time' => $request->time,
                    'status' =>'scheduled'
                ]);

                 if (!$schedule->peredicTable) {
                    $schedule->peredicTable()->create([ 
                        'live_room_id' => $liveRoomId,
                    ]);
                } else {
                    $schedule->peredicTable()->update([
                        'live_room_id' => $liveRoomId,
                    ]);
                }
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'message' => $this->langService->getLang('schedule_not_updated'),
                    'errors' => $e->getMessage(),
                ], 422);
            }

            return response()->json([
                'message' => $this->langService->getLang('schedule_updated'),
                'data' => new ScheduleResource($schedule),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $this->langService->getLang('schedules_not_found')], 404);
        }
    }
    public function destroy($id)
    {
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
            ->where('user_id', $user->id)
            ->whereHas('peredicTable.liveRoom', fn($query) => $query->where('instructor_id', $user->id))
            ->where('student_id', null)
            ->get();

        if (!$schedules) {
            return response()->json([
                'data' => 'no Schedules found'
            ]);
        }

        return response()->json([
            'data' => ScheduleResource::collection($schedules)
        ]);
    }

    public function getStudentSchedules() {
        $user = Auth::user();

        $groupRooms = GroupRoom::with('liveRoom.peredicTables')
            ->where('user_id', $user->id)
            ->get();

        $scheduleIds = [];

        foreach ($groupRooms as $groupRoom) {
            if ($groupRoom->liveRoom && $groupRoom->liveRoom->peredicTables) {
                foreach ($groupRoom->liveRoom->peredicTables as $peredicTable) {
                    $scheduleIds[] = $peredicTable->schedule_id;
                }
            }
        }

        $scheduleIds = array_unique($scheduleIds);

        $schedules = Schedule::whereIn('id', $scheduleIds)->get();

        return response()->json([
            'data' => ScheduleResource::collection($schedules)
        ]);
    }

    public function getStudentPrivateSchedules() {
        $user = Auth::user();
 

        $schedules = Schedule::query()
            ->where('student_id', $user->id)
            ->get();

        return response()->json([
            'data' => ScheduleResource::collection($schedules)
        ]);
    }

    public function addPrivateSchedule(Request $request)
    {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$user) {
            return;
        }

        $studentId = $request->student_id ?? null;

        $student = User::query()
            ->where('id', $studentId)
            ->first();

        if (!$student) {
            return response()->json([
                'message' => $this->langService->getLang('student_not_found')
            ], 422);
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

        try {
            DB::beginTransaction();

            $schedule = $user->schedules()->create([
                'day' => $request->day,
                'schedule_time' => $request->time,
                'room_name' => Str::uuid(),
                'student_id' => $studentId,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $this->langService->getLang('schedule_not_created'),
                'errors' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => $this->langService->getLang('schedule_created'),
            'data' => new ScheduleResource($schedule),
        ]);
    }

    public function updatePrivateSchedule(Request $request, $id) {
        try {

            $schedule = Schedule::query()
                ->where('user_id', Auth::id())
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

            try {
                DB::beginTransaction();

                $schedule->update([
                    'day' => $request->day,
                    'schedule_time' => $request->time,
                    'status' => 'scheduled'
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'message' => $this->langService->getLang('schedule_not_updated'),
                    'errors' => $e->getMessage(),
                ], 422);
            }

            return response()->json([
                'message' => $this->langService->getLang('schedule_updated'),
                'data' => new ScheduleResource($schedule),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $this->langService->getLang('schedules_not_found')], 404);
        }
    }

    public function todaySchedules() { 
 
        $schedules = Schedule::query()
            ->where('day', now()->format('l'))
            ->get();

        if (!$schedules) {
            return response()->json([
                'data' => 'no Schedules found'
            ]);
        }

        return response()->json([
            'data' => TodayScheduleResource::collection($schedules)
        ]);
    }
}
