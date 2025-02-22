<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Http\Resources\Course\CourseModuleResource;
use App\Models\Course\CourseModule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Services\LangService;

class CourseModuleController extends Controller
{


    protected $langService;

    public function __construct(LangService $langService) {
        $this->langService = $langService;
    }
    /**
     * Display a listing of the course modules.
     */
    public function index()
    {
        $courseModules = CourseModule::with('CourseContents')
            ->get();

        return response()->json([
            'data' => CourseModuleResource::collection($courseModules),
        ]);
    }

    /**
     * Store a newly created course module in storage.
     */
    public function store(Request $request) {
        // Ensure user is an admin or instructor
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized action.',
            ], 403);
        }

        // Validate request
        $validationRules = [
            'title' => 'required|string|min:3',
            'sequence' => 'required|numeric',
            'description' => 'nullable|string|min:10',
            'course_id' => 'required|exists:courses,id',
        ];

        $validator = Validator::make($request->all(), $validationRules,$this->langService->getLang('courses'));

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        // Create course module
        $courseModule = $user->courseModules()->create([
            'slug' => Str::uuid(),
            'title' => $request->title,
            'sequence' => $request->sequence,
            'description' => $request->description,
            'course_id' => $request->course_id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Course Module successfully created.',
            'data' => new CourseModuleResource($courseModule),
        ], 201);
    }

    /**
     * Display the specified course module.
     */
    public function show(int $id) {
        $courseModule = CourseModule::with('CourseContents')
            ->where('id', $id)
            ->first();
    
        if (!$courseModule) {
            return response()->json([
                'message' => "Course Module not found with ID: {$id}",
            ], 404);
        }
    
        return response()->json([
            'data' => new CourseModuleResource($courseModule),
        ]);
    }
    
    /**
     * Update the specified course module in storage.
     */
    public function update(Request $request, int $id)
    {
        // Ensure user is an admin or instructor
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();
    
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized action.',
            ], 403);
        }
    
        // Find the course module by ID
        $courseModule = CourseModule::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();
    
        if (!$courseModule) {
            return response()->json([
                'message' => "Course Module not found with ID: {$id}",
            ], 404);
        }
    
        // Define validation rules
        $validationRules = [
            'title' => 'nullable|string|min:3',
            'sequence' => 'nullable|numeric',
            'description' => 'nullable|string|min:10',
            'course_id' => 'nullable|exists:courses,id',
        ];
    
        $validator = Validator::make($request->all(), $validationRules);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }
    
        // Update the course module
        $courseModule->update($validator->validated());
    
        return response()->json([
            'message' => 'Course Module successfully updated.',
            'data' => new CourseModuleResource($courseModule),
        ]);
    }
    
    /**
     * Remove the specified course module from storage.
     */
    public function destroy(int $id)
    {
        // Ensure user is an admin or instructor
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();
    
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized action.',
            ], 403);
        }
    
        // Find the course module by ID
        $courseModule = CourseModule::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();
    
        if (!$courseModule) {
            return response()->json([
                'message' => "Course Module not found with ID: {$id}",
            ], 404);
        }
    
        // Delete the course module
        $courseModule->delete();
    
        return response()->json([
            'message' => 'Course Module successfully deleted.',
        ]);
    }
    
}
