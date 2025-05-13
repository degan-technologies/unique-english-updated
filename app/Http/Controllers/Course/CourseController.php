<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Http\Resources\Course\CourseResource;
use App\Http\Resources\Course\MyCourseResource;
use App\Http\Resources\StudentResources\StdCourse\StdCourseResource;
use App\Models\Course\Course;
use App\Models\User;
use App\Services\LangService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Course\CourseContent;
use App\Models\Quiz\QMetaData;
use App\Models\Quiz\Result;
use App\Models\Course\CourseContentProgress;


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
    public function allCourses() {
        /**
         * @var mixed Course $course
         */

        $courses = Course::query()
            ->where('status', PUBLISHED)
            ->paginate(10);

        $pagination = $courses->toArray();
        unset($pagination['data']);

        return response() -> json([
            'pagination' => $pagination,
            'data' => StdCourseResource::collection($courses)
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
            'skill_level' =>[Rule::in(SKILL_LEVEL)],
            'price' => 'numeric',
            'discount' => 'numeric',
            'credit_hour' => 'numeric',
            'thumbnail_url' => 'image',
            'intro_video' => 'nullable|file|mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime,video/3gpp,video/mov,video/x-msvideo,video/x-ms-wmv,video/webm,video/ogg,video/x-flv'
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('courses'));

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }
        $imagePath = null;
        if($request->hasFile('thumbnail_url')) {
            $imagePath = $request->file('thumbnail_url')->store('/course/images', 'public');
        }
        $videoPath = null;
        if($request->hasFile('intro_video')) {
            $videoPath = $request->file('intro_video')->store('course/video', 'public');
        }
        $course = $user->courses()->create([
            'slug' => Str::uuid(),
            'course_name' => $request->course_name,
            'overview' => $request->overview,
            'tag' =>json_encode($request->tag),
            'skill_level' => $request->skill_level,
            'price' => $request->price,
            'discount' => $request->discount,
            'credit_hour' => $request->credit_hour,
            'thumbnail_url' => $imagePath,
            'intro_video' => $videoPath,
            'language' => $request->language,
            'status' => $request->status
        ]);

        return response()->json([
        'message' => $this->langService->getLang('course_successfully_added'),
        'data' => new CourseResource($course),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $course = Course::with('courseModules')->where('id', $id)->first();
        if (!$course) {
            return response()->json(['error' => 'Course not found'], 404);
        }
    
        return response()->json([
            'data' => new CourseResource($course)
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
            -> where('user_id', $user->id)
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
            'skill_level' =>[Rule::in(SKILL_LEVEL)],
            'price' => 'numeric',
            'discount' => 'numeric',
            'credit_hour' => 'numeric',
            'thumbnail_url' => 'image',
            'intro_video' => 'nullable|file|mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime,video/3gpp,video/mov,video/x-msvideo,video/x-ms-wmv,video/webm,video/ogg,video/x-flv'

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

        if ($course->status === 'draft') {
            $data['status'] = 'published';  
        }

        if ($request->hasFile('thumbnail_url')) {

            if ($course->thumbnail_url) {
                Storage::disk('public')->delete($course->thumbnail_url);
            }
            $data['thumbnail_url'] = $request->file('thumbnail_url')->store('course/images', 'public');
        }

        if ($request->hasFile('intro_video')) {
            if ($course->intro_video) {
                Storage::disk('public')->delete($course->intro_video);
            }
            $data['intro_video'] = $request->file('intro_video')->store('course/video', 'public');
        }

        $course->update($data);

        return response()->json([
            'message' => $this->langService->getLang('course_successfully_updated'),
            'data' => new CourseResource($course),
        ]);
    }

    public function certificateStatus(Request $request, $course_id)
    {
        $user = Auth::user();
        $allContentsCompleted= false;
        $allQuizzesCompleted = false;

        $courseContents = CourseContent::where('course_id', $course_id)->get();
        $countLessons = $courseContents->count();
        $countCompletedLesson = $courseContents->filter(function ($content) use ($user) {
            return $content->courseContentProgress()
                ->where('user_id', $user->id)
                ->where('progress', 'completed')
                ->exists();
        })->count();

        if($countLessons && $countCompletedLesson === $countLessons){
            $allContentsCompleted = true;
        }

        $qMetaDataRecords = QMetaData::where('course_id', $course_id)->get();
        $countExams = $qMetaDataRecords->count();
        $countResult = $qMetaDataRecords->filter(function ($exam) use ($user) {
            return $exam->results()
                ->where('user_id', $user->id)
                ->where('result', '>=', 0)
                ->exists();
        })->count();

        if ($countExams && $countCompletedLesson === $countExams) {
            $allQuizzesCompleted = true;
        }

        return response()->json([
            'certificate_active' => $allContentsCompleted && $allQuizzesCompleted,
            'allContentsCompleted' => $allContentsCompleted,
            'allQuizzesCompleted' => $allQuizzesCompleted,
        ]);
    }

    public function updateProgress(Request $request, $courseContentId)
    {
        $user = $request->user();

        $validated = $request->validate([
            'progress' => 'required|string', 
        ]);

        $progress = CourseContentProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'course_content_id' => $courseContentId,
            ],
            [
                'progress' => $validated['progress'],
            ]
        );

        return response()->json([
            'message' => 'Progress updated successfully.',
            'data' => $progress,
        ]);
    }



    public function destroy(string $id) {

        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();
            
        if (!$user) return;

        $course = Course::query()
          
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

    public function search(Request $request) {
        $courses = Course::where('user_id', Auth::id())
            ->when($request->searchQuery, fn($q) => $q->where('course_name', 'like', "%{$request->searchQuery}%"))
            ->when($request->skillLevel, fn($q) => $q->where('skill_level', $request->skillLevel))
            ->paginate($request->rowsPerPageOptions);
     
        $stats = Course::query()
            ->where('user_id', Auth::id())
            ->selectRaw(
                'COUNT(*) as total, SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as newToday',
                [Carbon::now()->format('Y-m-d')]
            )
            ->first();
    
        $pagination = $courses->toArray();
        unset($pagination['data']);
    
        return response()->json([
            'newToday'   => $stats->newToday,
            'total'      => $stats->total,
            'pagination' => $pagination,
            'data'       => CourseResource::collection($courses)
        ]);
    }


    public function myCourse() {
        $user = Auth::user();

        $courses = Course::query()
            ->whereHas('transactions', function ($query) use ($user) {
                $query->where('status', TRANSACTION_SUCCESS)
                    ->where('customer_id', $user->id);
            })
            ->get();
        
        if(!$courses) {
            return response()->json([
                'message' => $this->langService->getLang('course_not_found'),
            ], 404);
        }

        return response()->json([
            'data' => MyCourseResource::collection($courses)
        ]);
    }
    
}
