<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Http\Resources\Course\CourseContentResource;
use App\Models\Course\Course;
use App\Models\Course\CourseContent;
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        /**
         * @var App\Models\User $user
         */
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();
            
        if (!$user) return;

        $courseId = $request->course_id ?? null;

        $course = Course::query()
                ->where('user_id', $user->id)
                ->find($courseId);
        
        if(!$course) {
            return response()->json([
                'message' => $this->langService->getLang('course_not_found')
            ], 404);
        }

        $courseContent = CourseContent::query()
                        ->where('course_id', $courseId)
                        ->orderBy('sequence', 'desc')
                        ->first();

        $sequence = 1;

        if($courseContent) {
            $sequence = $courseContent->sequence + 1;
        }

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
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }

        
        $filePath = null;
        $imagePath = null;

        if ($request->hasFile('content_url')) {
            $file = $request->file('content_url');
            $filePath = $file->store('/course', 'public');
        }

        if ($request->hasFile('thumbnail_url')) {
            $file = $request->file('thumbnail_url');
            $imagePath = $file->store('/course', 'public');
        }

        $courseContent = $user->courseContents()->create([
            'course_id' => $courseId,
            'slug' => Str::uuid(),
            'title' => $request->title,
            'description' => $request->description,
            'content_type' => $request->content_type,
            'content_url' => $filePath ?? null,
            'thumbnail_url' => $imagePath ?? null,
            'hour' => $request->hour,
            'status' => $request->status,
            'sequence' => $sequence,
            'note' => $request->note,
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

        $courseContent = CourseContent::query()
            ->where('user_id', $user->id)
            ->findOrFail($id);

        if (!$courseContent) {
            return response()->json([
                'message' => $this->langService->getLang('course_content_not_found'),
            ], 404);
        }

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

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('courses'));

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }

        $courseContent->update($validator->validated());

        return response()->json([
            'message' => $this->langService->getLang('course_successfully_updated'),
            'data' => new CourseContentResource($courseContent),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) {

        /**
         * @var App\Models\User $user;
         */

        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$user) return;

        $courseContent = CourseContent::query()
            ->where('user_id', $user->id)
            ->findOrFail($id);

        if (!$courseContent) {
            return response()->json([
                'message' => $this->langService->getLang('course_content_not_found'),
            ], 404);
        }

        $courseContent->delete();

        return response()->json([
            'message' => $this->langService->getLang('course_content_successfully_deleted'),
        ]);
    }
}
