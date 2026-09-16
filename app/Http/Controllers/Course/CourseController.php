<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Http\Resources\Course\CourseResource;
use App\Http\Resources\Course\MyCourseResource;
use App\Http\Resources\StudentResources\StdCourse\StdCourseResource;
use App\Jobs\ProcessCourseVideo;
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
use App\Models\Course\CourseContentProgress;
use App\Models\Quiz\Result;


class CourseController extends Controller
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

    /**
     * Display a listing of the resource.
     */
    public function allCourses()
    {
        /**
         * @var mixed Course $course
         */ 
        $courses = Course::query()
            ->with([
                'user',
                'feedBacks:id,course_id,rate',
            ])
            ->where('status', PUBLISHED)
            ->paginate(10);

        $pagination = $courses->toArray();
        unset($pagination['data']);

        return response()->json([
            'pagination' => $pagination,
            'data' => StdCourseResource::collection($courses)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /**
         * @var App\Models\User $user
         */

        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$user) return;

        $validationRules = [
            'course_name' => ['required'],
            'overview' => 'min:10',
            'skill_level' => [Rule::in(SKILL_LEVEL)],
            'price' => 'numeric',
            'thumbnail_url' => 'required',
            'intro_video' => 'required',
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
            'tag' => json_encode(['courses']),
            'skill_level' => $request->skill_level,
            'price' => $request->price,
            'discount' => 0,
            'credit_hour' => 0,
            'thumbnail_url' => $request->thumbnail_url,
            'intro_video' => $request->intro_video,
            'language' => $request->language,
            'status' => DRAFT,
            'video_optimized' => true,
        ]);

        if ($request->intro_video !== null) {
            ProcessCourseVideo::dispatch($course->intro_video, $course->id)->onQueue('video-processing');
        }

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

    public function showCourse(string $slug)
    {
        $course = Course::with('courseModules')->where('slug', $slug)->first();
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
    public function update(Request $request, string $id)
    {
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

        $validationRules = [
            'course_name' => ['required'],
            'overview' => 'min:10',
            'skill_level' => [Rule::in(SKILL_LEVEL)],
            'price' => 'numeric',
            'thumbnail_url' => 'nullable',
            'intro_video' => 'nullable'
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

        if ($request->thumbnail_url !== null) {

            if ($course->thumbnail_url) {
                Storage::disk('s3')->delete($course->thumbnail_url);
            }
            $data['thumbnail_url'] = $request->thumbnail_url;
        }

        if ($request->intro_video !== null) {
            if ($course->intro_video && $course->intro_video !== $request->intro_video) {
                Storage::disk('s3')->delete($course->intro_video);
            } 

            $data['intro_video'] = $request->intro_video;
            $data['video_optimized'] = true;
        }

        $course->update($data);
        // $course->refresh();

        if ($request->intro_video !== null) { 
            ProcessCourseVideo::dispatch($course->intro_video, $course->id)->onQueue('video-processing');
        }

        return response()->json([
            'message' => $this->langService->getLang('course_successfully_updated'),
            'data' => new CourseResource($course),
        ]);
    }

    public function updateStatus(Request $request, string $id)
    {
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


        $course->update([
            'status' => $course->status === PUBLISHED ? DRAFT : PUBLISHED,
        ]);

        return response()->json([
            'message' => $this->langService->getLang('course_successfully_updated'),
        ]);
    }

    public function certificateStatus(Request $request, $course_id)
    {
        $user = Auth::user();
        $courseContentsCount = CourseContent::query()
            ->where('course_id', $course_id)
            ->count();

        $completedContentsCount = CourseContentProgress::query()
            ->where('user_id', $user->id)
            ->where('progress', 'completed')
            ->whereIn('course_content_id', function ($query) use ($course_id) {
                $query->select('id')
                    ->from('course_contents')
                    ->where('course_id', $course_id);
            })
            ->count();

        $allContentsCompleted = $courseContentsCount > 0
            && $completedContentsCount === $courseContentsCount;

        $examCount = QMetaData::query()
            ->where('course_id', $course_id)
            ->count();

        $completedExamCount = Result::query()
            ->where('user_id', $user->id)
            ->where('result', '>=', 0)
            ->whereHas('qMetaData', function ($query) use ($course_id) {
                $query->where('course_id', $course_id);
            })
            ->count();

        $allQuizzesCompleted = $examCount > 0
            && $completedExamCount === $examCount;

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



    public function destroy(string $id)
    {

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

    public function search(Request $request)
    {
        $courses = Course::where('user_id', Auth::id())
            ->withCount([
                'transactions as total_enroll' => fn($q) => $q->where('status', 'success'),
            ])
            ->withSum([
                'transactions as total_revenue' => fn($q) => $q->where('status', 'success'),
            ], 'amount')
            ->when($request->searchQuery, fn($q) => $q->where('course_name', 'like', "%{$request->searchQuery}%"))
            ->when($request->skillLevel, fn($q) => $q->where('skill_level', $request->skillLevel))
            ->paginate($request->rowsPerPageOptions ?? 10);

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


    public function myCourse()
    {
        $user = Auth::user();

        $courses = Course::query()
            ->whereHas('transactions', function ($query) use ($user) {
                $query->where('status', TRANSACTION_SUCCESS)
                    ->where('customer_id', $user->id);
            })
            ->get();

        if (!$courses) {
            return response()->json([
                'message' => $this->langService->getLang('course_not_found'),
            ], 404);
        }

        return response()->json([
            'data' => MyCourseResource::collection($courses)
        ]);
    }

    public function uploadIntroVideo(Request $request)
    {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$user) {
            return;
        }

        $request->validate([
            'intro_video' => 'required|file|mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime,video/3gpp,video/mov,video/x-msvideo,video/x-ms-wmv,video/webm,video/ogg,video/x-flv'
        ]);

        $file = $request->file('intro_video');
        $path = Storage::disk('s3')->putFile('course/video/original', $file);

        return response()->json([
            'message' => 'Video uploaded successfully',
            'path' => $path
        ]);
    }

    public function uploadThumbnail(Request $request)
    {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$user) {
            return;
        }

        $request->validate([
            'thumbnail_url' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $file = $request->file('thumbnail_url');
        $path = Storage::disk('s3')->putFile('course/images', $file);

        return response()->json([
            'message' => 'Thumbnail uploaded successfully',
            'path' => $path
        ]);
    }
}
