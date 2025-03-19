<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\Course\CourseContentProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CourseContentProgressController extends Controller
{
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
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'user_id'           => 'required|integer|exists:users,id',
            'course_content_id' => 'required|integer|exists:course_contents,id',
            'progress'          => 'required|numeric|min:0|max:100',
            'course_id'         => 'sometimes|nullable|integer|exists:courses,id',
            'course_module_id'  => 'sometimes|nullable|integer|exists:course_modules,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error'    => 'Validation error',
                'messages' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Update the progress if it exists, otherwise create a new record
        $progressRecord = CourseContentProgress::updateOrCreate(
            [
                'user_id'           => $data['user_id'],
                'course_content_id' => $data['course_content_id']
            ],
            [
                'progress'          => $data['progress'],
                'course_id'         => $data['course_id'] ?? null,
                'course_module_id'  => $data['course_module_id'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Progress saved successfully',
            'data'    => $progressRecord
        ], 200);
    }


    public function index(Request $request)
{
    $userId = $request->query('user_id');
    if (!$userId) {
        return response()->json(['error' => 'User ID is required'], 400);
    }

    // Retrieve all progress records for the given user
    $progressRecords = CourseContentProgress::where('user_id', $userId)->get();

    if ($progressRecords->isEmpty()) {
        return response()->json(['message' => 'No progress found'], 404);
    }

    return response()->json([
        'data' => $progressRecords
    ], 200);
}


    /**
     * Retrieve progress for a given lesson and user.
     *
     * You can call this endpoint with query parameter "user_id" (or use your auth middleware)
     * Example: GET /api/coursecontent/progress/{courseContentId}?user_id=1
     */
    public function show(Request $request, $courseContentId)
    {
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
}
