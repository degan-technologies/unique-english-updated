<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Http\Resources\Course\CourseContentResource;
use App\Models\Course\Course;
use App\Models\Course\CourseContent;
use App\Models\Course\CourseModule;
use App\Models\User;
use App\Services\LangService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class CourseContentController extends Controller {

    /**
     * get error traslation and success beased on the language 
     * localized 
     */
    protected $langService;

    public function __construct(LangService $langService) {
        $this->langService = $langService;
    }



    public function index() {
        $courses = CourseContent::all();
        return response() -> json([
            'data' => CourseContentResource::collection($courses)
        ]);
    
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $user = User::query()->whereSystemAdminOrInstructor()->first();
        if (!$user) return;
    
        $moduleId = $request->course_module_id ?? null;
    
        $courseModule = CourseModule::query()
            ->where('user_id', $user->id)
            ->find($moduleId);
    
        if (!$courseModule) {
            return response()->json([
                'message' => $this->langService->getLang('course_not_found')
            ], 404);
        }
    
        $courseContent = CourseContent::query()
            ->where('course_module_id', $moduleId)
            ->orderBy('sequence', 'desc')
            ->first();
    
        $sequence = $courseContent ? $courseContent->sequence + 1 : 1;
    
        $validationRules = [
            'title' => ['required', 'not_regex:/[\\\\\/\?\%\*\:\|\"<>]/', 'min:4'],
            'description' => 'min:10',
            'content_type' => [Rule::in(CONTENT_TYPE)],
            'content_url' => 'file',
            'thumbnail_url' => 'image',
            'hour' => 'date_format:H:i',
            'status' => [Rule::in(COURSE_STATUS)],
            'note' => 'min:10',
        ];
    
        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('courseContent'));
    
        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->all()[0],
                'errors' => $validator->errors()
            ], 422);
        }
    
        $filePath = $request->hasFile('content_url')
            ? $request->file('content_url')->store('/course', 'public')
            : null;
    
            $imagePath = null;
            if($request->hasFile('thumbnail_url')) {
                $imagePath = $request->file('thumbnail_url')->store('/course', 'public');
            }
    
        $courseContent = $user->courseContents()->create([
            'course_module_id' => $moduleId,  // Change from course_id to course_module_id
            'course_id' => $request->course_id,
            'slug' => Str::uuid(),
            'title' => $request->title,
            'description' => $request->description,
            'content_type' => $request->content_type,
            'content_url' => $filePath,
            'thumbnail_url' => $imagePath,
            'hour' => $request->hour,
            'status' => $request->status,
            'sequence' => $sequence,
            'isDownloadable' => false,
        ]);
    
        return response()->json([
            'message' => $this->langService->getLang('course_content_successfully_added'),
            'data' => new CourseContentResource($courseContent),
        ]);
    }
    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();
    
        if (!$user) return;
    
        $courseContent = CourseContent::findOrFail($id);
    
        if (!$courseContent) {
            return response()->json([
                'message' => $this->langService->getLang('course_content_not_found'),
            ], 404);
        }
    
        $validationRules = [
            'title' => ['required', 'not_regex:/[\\\\\/\?\%\*\:\|\"<>]/', 'min:4'],
            'description' => 'min:10',
            'content_type' => [Rule::in(CONTENT_TYPE)],
            'content_url' => 'file', // Just validates that a file is present, does not store it.
            'thumbnail_url' => 'image', // Same here.
            'hour' => 'date_format:H:i',
            'status' => [Rule::in(COURSE_CONTENT_STATUS)],
        ];
    
        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('courses'));
    
        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];
            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }
    
        $data = $validator->validated();
    
        // Process file uploads:
        if ($request->hasFile('content_url')) {
            // Store file in the "course" folder on the "public" disk
            $data['content_url'] = $request->file('content_url')->store('course', 'public');
        }
    
        if ($request->hasFile('thumbnail_url')) {
            $data['thumbnail_url'] = $request->file('thumbnail_url')->store('course', 'public');
        }
    
        // Update the record with the processed data.
        $courseContent->update($data);
    
        return response()->json([
            'message' => $this->langService->getLang('course_successfully_updated'),
            'data' => new CourseContentResource($courseContent),
        ]);
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();
    
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    
        $courseContent = CourseContent::where('id', $id)->first();
    
        if (!$courseContent) {
            return response()->json([
                'message' => $this->langService->getLang('course_content_not_found'),
            ], 404);
        }
    
        try {
            $courseContent->delete();
            return response()->json([
                'message' => $this->langService->getLang('course_content_successfully_deleted'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting content',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}
