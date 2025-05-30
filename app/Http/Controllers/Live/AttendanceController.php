<?php

namespace App\Http\Controllers\Live;

use App\Http\Controllers\Controller;
use App\Models\Live\InsAttendance;
use App\Models\Live\StdAttendance;
use App\Models\Schedule\Schedule;
use App\Models\User;
use App\Services\LangService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * get error traslation and success beased on the language 
     * localized 
     */
    protected $langService;

    public function __construct(LangService $langService)
    {
        $this->langService = $langService;
    }

    public function storeInstractorAttendance(Request $request, $scheduleId)
    {
        $instractor = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$instractor) return;

        $schedule = Schedule::query()
            ->where('id', $scheduleId)
            ->where('user_id', $instractor->id)
            ->first();

        if (!$schedule) {
            return response()->json([
                'data' => 'Schedule not found',
            ], 404);
        }

        DB::beginTransaction();
        try {

            $schedule->update([
                'status' => 'live'
            ]);

            $attendance = $instractor->insAttendances()->create([
                'start_time' => Carbon::now(),
                'schedule_id' => $schedule->id,
                'created_at' => Carbon::now(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' =>  $e->getMessage(),
            ], 404);
        }

        return response()->json([
            'data' => 'success',
        ]);
    }


    public function updateInstractorAttendance(Request $request, $scheduleId)
    {
        $instractor = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$instractor) return;

        $attendance = InsAttendance::query()
            ->where('schedule_id', $scheduleId)
            ->where('instructor_id', $instractor->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$attendance) {
            return response()->json([
                'data' => 'Attendance not found',
            ], 404);
        };

        $schedule = Schedule::query()
            ->where('id', $scheduleId)
            ->where('status', 'live')
            ->first();

        if (!$schedule) {
            return response()->json([
                'data' => 'schedule not found',
            ], 404);
        }

        try {
            DB::beginTransaction();

            $attendance->update([
                'end_time' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $schedule->update([
                'status' => 'completed'
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => 'somthing wont wrong',
            ], 404);
        }

        return response()->json([
            'data' => 'success',
        ]);
    }

    public function updateVisiterAttendance(Request $request, $scheduleId)
    {
        $visiter = User::query()
            ->where('id', Auth::id())
            ->has('systemAdmin')
            ->first();

        if (!$visiter) return;

        $schedule = Schedule::query()
            ->where('id', $scheduleId)
            ->where('status', 'live')
            ->first();

        if (!$schedule) {
            return response()->json([
                'data' => 'Schedule not in Live mode',
            ], 404);
        }

        $attendance = InsAttendance::query()
            ->where('schedule_id', $schedule->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$attendance) {
            return response()->json([
                'data' => 'Attendance not found',
            ], 404);
        }

        $attendance->update([
            'visiter_id' => $visiter->id,
        ]);

        return response()->json([
            'data' => 'success',
        ]);
    }

    public function storeStudentAttendance(Request $request, $scheduleId)
    {
        $student = User::query()
            ->where('id', Auth::id())
            ->first();

        if (!$student) return;

        $schedule = Schedule::query()
            ->where('id', $scheduleId)
            ->where('status', 'live')
            ->first();

        if (!$schedule) {
            return response()->json([
                'data' => 'Schedule not found',
            ], 404);
        }

        $attendance = InsAttendance::query()
            ->where('schedule_id', $schedule->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $attendance = $student->stdAttendances()->create([
            'start_time' => Carbon::now(),
            'schedule_id' => $schedule->id,
            'ins_attendance_id' => $attendance->id,
            'created_at' => Carbon::now(),
        ]);

        return response()->json([
            'data' => 'success',
        ]);
    }

    public function updateStudentAttendance(Request $request, $scheduleId)
    {
        $student = User::query()
            ->where('id', Auth::id())
            ->first();

        if (!$student) return;

        $attendance = StdAttendance::query()
            ->where('schedule_id', $scheduleId)
            ->where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$attendance) return;

        $attendance->update([
            'end_time' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return response()->json([
            'data' => 'success',
        ]);
    }

    public function instractorAttendance(Request $request)
    {
        $supervisor = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$supervisor) {
            return response()->json([
                'data' => 'Supervisor not found',
            ], 404);
        }

        $instructors = User::query()
            ->where(function ($query) use ($supervisor) {
                $query->where('user_id', $supervisor->id)
                    ->orWhere('id', $supervisor->id);
            })
            ->paginate($request->rowsPerPageOptions);

        if ($instructors->isEmpty()) {
            return response()->json([
                'data' => 'Instructors not found',
            ], 404);
        }

        $pagination = $instructors->toArray();
        unset($pagination['data']);

        $data = [];

        foreach ($instructors as $instructor) {
            $attendances = $instructor->insAttendances()
                ->whereNotNull('start_time')
                ->whereNotNull('end_time')
                ->whereHas('stdAttendances')
                ->get();

            // Calculate total hours and class count
            $totalSeconds = 0;
            foreach ($attendances as $attendance) {
                $start = Carbon::parse($attendance->start_time);
                $end = Carbon::parse($attendance->end_time);
                if ($end->greaterThan($start)) {
                    $totalSeconds += abs($end->diffInSeconds($start));
                }
            }

            $data[] = [
                'id'            => $instructor->id,
                'first_name'    => $instructor->first_name,
                'middle_name'   => $instructor->middle_name,
                'email'         => $instructor->email,
                'total_hours'   => $totalSeconds !== 0 ? round($totalSeconds / 3600, 2) : 'not Start',
                'total_classes' => $totalSeconds !== 0 ? $attendances->count() : 'not Start',
            ];
        }

        return response()->json([
            'data' => $data,
            'pagination' => $pagination,
        ]);
    }

    public function detailInstractorAttendance(Request $request, $id)
    {
        $supervisor = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$supervisor) {
            return response()->json([
                'data' => 'Supervisor not found',
            ], 404);
        }

        $instructor = User::query()
            ->where('id', $id)
            ->where(function ($query) use ($supervisor) {
                $query->where('user_id', $supervisor->id)
                    ->orWhere('id', $supervisor->id);
            })
            ->first();

        $attendances = $instructor->insAttendances()
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->whereHas('stdAttendances')
            ->paginate($request->rowsPerPageOptions);

        if ($attendances->count() === 0) {
            return response()->json([
                'data' => 'Attendances not found',
            ], 404);
        }

        $pagination = $attendances->toArray();
        unset($pagination['data']);

        $data = [];

        foreach ($attendances as $attendance) {
            $start = Carbon::parse($attendance->start_time);
            $end = Carbon::parse($attendance->end_time);
            $totalSeconds = abs($end->diffInSeconds($start));
            $data[] = [
                'created_at' => $attendance->created_at->format('l, F j, Y'),
                'start_time' => $attendance->start_time->format('h:i A'),
                'end_time' => $attendance->end_time->format('h:i A'),
                'total_hours' => round($totalSeconds / 3600, 2),
                'total_students' => $attendance->stdAttendances->count(),
            ];
        }

        return response()->json([
            'data' => $data,
            'pagination' => $pagination,
        ]);
    }
}
