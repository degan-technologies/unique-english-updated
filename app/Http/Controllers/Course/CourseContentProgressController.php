<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Http\Resources\Course\CourseContentResource;
use App\Http\Resources\Course\CourseModuleResource;
use App\Models\Course\Course;
use App\Models\Course\CourseContent;
use App\Models\Course\CourseContentProgress;
use App\Models\Course\CourseModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CourseContentProgressController extends Controller {

    public function index(Request $request) {
        

        $progressRecords = CourseContentProgress::where('user_id', Auth::id())->get();

        if ($progressRecords->isEmpty()) {
            return response()->json(['message' => 'No progress found'], 404);
        }

        return response()->json([
            'data' => $progressRecords
        ], 200);
    }

    /**
     * Store or update a lesson’s progress.
     *
     * Expected POST data:
     * - user_id: ID of the user (you might get this from your auth system)
     * - course_content_id: ID of the lesson
     * - progress: Numeric value (0-100)
     * - course_id: (optional) ID of the course
     * - course_module_id: (optional) ID of the module
     */

    public function store(Request $request) {

        /**
         * Get the authenticated user
         * @var User $user
         */

        $user = Auth::user();
        $courseContentId = $request->course_content_id ?? null;
        $videoProgress = $request->progress;

        $courseContent = CourseContent::query()
            ->where('id', $courseContentId)
            ->first();

        if (!$courseContent) {
            return response()->json([
                'error' => 'Course content not found'
            ], 404);
        }

        if ($courseContent->content_type !== VIDEO) {
            return response()->json([
                'error' => 'Only video content can be marked as progress'
            ], 400);
        }

        $eligibleCourse = Course::checkEligibility($courseContent->course_id);

        if (!$eligibleCourse) {
            return response()->json([
                'message' => 'Unauthorized action.'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'progress' => ['required', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error'    => 'Validation error',
                'messages' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        $getProgress = $courseContent->courseContentProgress()->first();

        if(!$getProgress) {
           
            $progressRecord = $user->courseContentProgress()->create([
                'progress'          => $videoProgress,
                'course_content_id' => $courseContentId,
                'vedeo_progress' => $videoProgress,
                'course_id' => $courseContent->course_id,
            ]);

            return response()->json([
                'message' => 'Progress saved successfully',
                'data'    => $progressRecord
            ], 200);
        }

        $progress =  $getProgress->progress;

        if ($videoProgress > $progress) {
            $progress = $videoProgress;
        }


        $progressRecord = $getProgress->update([
            'progress' => $progress,
            'vedeo_progress' => $videoProgress,
            'course_id' => $courseContent->course_id,
        ]);

        return response()->json([
            'message' => 'Progress saved successfully',
            'data'    => $progressRecord
        ], 200);
    }

    /**
     * Retrieve progress for a given lesson and user.
     *
     * You can call this endpoint with query parameter "user_id" (or use your auth middleware)
     * Example: GET /api/coursecontent/progress/{courseContentId}?user_id=1
     */
    public function show(Request $request, $courseContentId) {
        $userId = $request->query('user_id');
        if (!$userId) {
            return response()->json(['error' => 'User ID is required'], 400);
        }

        $progressRecord = CourseContentProgress::where('user_id', $userId)
            ->where('course_content_id', $courseContentId)
            ->first();

        if (!$progressRecord) {
            return response()->json(['message' => 'No progress found'], 404);
        }

        return response()->json([
            'data' => $progressRecord
        ], 200);
    }


    public function currentProgress($slug) {
        $user = Auth::user();
        $overAllPogress = 0;

        $course = Course::query()
            ->where('slug', $slug)
            ->first();

        $eligibleCourse = Course::checkEligibility($course->id);

        if (!$eligibleCourse) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        $progress = CourseContentProgress::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->orderBy('id', 'desc')
            ->first();

        $totalSeconds = $course->courseContents()->where('content_type', VIDEO)->get()->reduce(function ($carry, $content) {
            $timeParts = explode(':', $content->hour);
            $seconds = ($timeParts[0] * 3600) + ($timeParts[1] * 60) + $timeParts[2];
            return $carry + $seconds;
        }, 0);

        $courseHours = floor($totalSeconds / 3600);
        $courseMinutes = floor(($totalSeconds % 3600) / 60);
        $courseSeconds = $totalSeconds % 60;

        $overAllCreditHour = sprintf('%02d:%02d:%02d', $courseHours, $courseMinutes, $courseSeconds);

        if($progress) {
            $totalTimeInSeconds = $progress->where('course_id', $course->id)->get()->reduce(function ($carry, $content) {
                $timeParts = explode(':', $content->progress);
                $seconds = ($timeParts[0] * 3600) + ($timeParts[1] * 60) + $timeParts[2];
                return $carry + $seconds;
            }, 0);
            $overAllPogress = round($totalTimeInSeconds / $totalSeconds * 100, 0);
        }


        if(!$overAllPogress) {
            $overAllPogress = 0;
        }

        if (!$progress) {
            $courseModule = $course->courseModules()->first();
            $courseContent = $courseModule->courseContents()->first();

            return response()->json([
                'courseModule' => new CourseModuleResource($courseModule),
                'courseContent' => new CourseContentResource($courseContent),
                'overAllPogress' => $overAllPogress,
            ], 200);
        }

        $courseModule = CourseModule::query()
            ->where('course_id', $course->id)
            ->first();
            
        $courseContent = $courseModule->courseContents()->where( 'id', $progress->course_content_id)->first();

        return response()->json([
            'courseModule' =>  new CourseModuleResource($courseModule),
            'courseContent' => new CourseContentResource($courseContent),
            'overAllPogress' => $overAllPogress,
        ], 200);
    }
}
