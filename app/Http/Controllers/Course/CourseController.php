<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Http\Resources\Course\CourseResource;
use App\Models\Course\Course;
use App\Models\User;
use App\Services\LangService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CourseController extends Controller {

    /**
     * get error traslation and success beased on the language 
     * localized 
     */
    protected $langService;

    public function __construct(LangService $langService) {
        $this->langService = $langService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index() {
        $courses = Course::all();

        return response() -> json([
            'data' => CourseResource::collection($courses)
        ]);
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

        if(!$user) return;
        
        $validationRules = [
            'course_name' => ['required', 'not_regex:/[\\\\\/\?\%\*\:\|\"<>]/'],
            'overview' => 'min:10',
            'tag' => 'min:3',
            'skill_level' => 'numeric',
            'price' => 'numeric',
            'discount' => 'numeric',
            'credit_hour' => 'numeric',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('courses'));

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }

        $course = $user->courses()->create([
            'slug' => Str::uuid(),
            'course_name' => $request->course_name,
            'overview' => $request->overview,
            'tag' =>json_encode($request->tag),
            'skill_level' => $request->skill_level,
            'price' => $request->price,
            'discount' => $request->discount,
            'credit_hour' => $request->credit_hour
        ]);

        return response()->json([
        'message' => $this->langService->getLang('course_successfully_added'),
        'data' => new CourseResource($course),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {
        $course = Course::query()
            ->where('user_id', Auth::user()->id)
            ->first();

        return response()->json([
            'data' => new  CourseResource($course)
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

        $course = Course::query()
            ->where('user_id', $user->id)
            ->findOrFail($id);

        if(!$course) {
            return response()->json([
                'message' => $this->langService->getLang('course_not_found'),
            ], 404);
        }

        $validationRules = [
            'course_name' => ['required', 'not_regex:/[\\\\\/\?\%\*\:\|\"<>]/'],
            'overview' => 'min:10',
            'tag' => 'min:3',
            'skill_level' => 'numeric',
            'price' => 'numeric',
            'discount' => 'numeric',
            'credit_hour' => 'numeric'
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('courses'));

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }

        $course->update($validator->validated());

        return response()->json([
            'message' => $this->langService->getLang('course_successfully_updated'),
            'data' => new CourseResource($course),
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

        $course = Course::query()
            ->where('user_id', $user->id)
            ->findOrFail($id);

        if (!$course) {
            return response()->json([
                'message' => $this->langService->getLang('course_not_found'),
            ], 404);
        }

        $course->delete();

        return response()->json([
            'message' => $this->langService->getLang('course_successfully_deleted'),
        ]);
    }
}
