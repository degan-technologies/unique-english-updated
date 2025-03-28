<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Http\Resources\Course\CourseModuleResource;
use App\Models\Course\Course;
use App\Models\Course\CourseModule;
use App\Http\Resources\Quiz\QASectionResource;
use App\Models\Quiz\QASection;
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

    public function getCourseModules($slug) {
        $user = Auth::user();

        $courseId = Course::query()
            ->where('slug', $slug)
            ->value('id');

        $verifyTransaction = Course::checkEligibility($courseId);

        if (!$verifyTransaction) {
            return response()->json([
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $courseModules = CourseModule::query()
            ->where('course_id', $courseId)
            ->get();
        
        $qaSections = QASection::query()
            ->where('course_id', $courseId)
            ->get();

        return response()->json([
            'data' => CourseModuleResource::collection($courseModules),
            'qaSections' => QASectionResource::collection($qaSections),
        ]);
    }

    /**
     * Store a newly created course module in storage.
     */
    public function store(Request $request) {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $getSequence = CourseModule::query()
                    ->where('course_id', $request->course_id)
                    ->orderBy('sequence', 'desc')
                    ->first();

        $validationRules = [
            'title' => 'required|string|min:3',
            'description' => 'required|string|min:10',
            'course_id' => 'required|exists:courses,id',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('modules'));

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $courseModule = $user->courseModules()->create([
            'slug' => Str::uuid(),
            'title' => $request->title,
            'sequence' =>$getSequence ? $getSequence->sequence + 1 : 1,
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
    public function show($id) {
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
    public function update(Request $request, $id) { // Ensure user is an admin or instructor
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized action.',
            ], 403);
        }
    
        $courseModule = CourseModule::query()
            ->where('user_id', $user->id)
            ->findOrFail($id);

    
        if (!$courseModule) {
            return response()->json([
                'message' => "Course Module not found with ID: {$id}",
            ], 404);
        }
    
        $validationRules = [
            'title' => 'required|string|min:3',
            'description' => 'required|string|min:10',
        ];
    
        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('modules'));
    
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }
    
        $courseModule->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);
    
        return response()->json([
            'message' => 'Course Module successfully updated.',
            'data' => new CourseModuleResource($courseModule),
        ]);
    }
    
    /**
     * Remove the specified course module from storage.
     */
    public function destroy($id)
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
