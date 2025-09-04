<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Http\Resources\Course\CourseContentResource;
use App\Jobs\ProcessLessonVideo;
use App\Models\Course\Course;
use App\Models\Course\CourseContent;
use App\Models\Course\CourseModule;
use App\Models\Transaction\Transaction;
use App\Models\User;
use App\Services\LangService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use getID3;
use Illuminate\Support\Facades\Storage;

class CourseContentController extends Controller {

    /**
     * get error traslation and success beased on the language 
     * localized 
     */
    protected $langService;

    public function __construct(LangService $langService) {
        $this->langService = $langService;
    }
 
    public function getModuleContents(Request $request, $moduleId) {
        /**
         * @var User $courseContent
         */
        $courseContent = CourseContent::query()
            ->where('course_module_id', $moduleId)
            ->when($request->contentType, fn($q) => $q->where('content_type', 'like', "%{$request->contentType}%"))
            ->paginate($request->rowsPerPageOption);

        $pagination = $courseContent->toArray();
        unset($pagination['data']);

        return response() -> json([
            'data' => CourseContentResource::collection($courseContent),
            'pagination' => $pagination,
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) { 
        $user = User::query()->whereSystemAdminOrInstructor()->first();
        if (!$user) return;
    
        $moduleId = $request->course_module_id ?? null;
        $contentType = (int) ($request->content_type ?? 0);

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
    
        $rules = [
            'title' => ['required',  'min:4'],  
            'content_url' => ['required'],
        ]; 

        $validator = Validator::make($request->all(), $rules, $this->langService->getLang('courseContent'));
    
        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->all()[0],
                'errors' => $validator->errors()
            ], 422);
        } 

        // Create the course content record
        $courseContent = $user->courseContents()->create([
            'course_module_id' => $moduleId,  
            'course_id' => $courseModule->course_id,
            'slug' => Str::uuid(),
            'title' => $request->title,
            'description' => 'well described',
            'content_type' => $contentType,  
            'content_url' => $request->content_url,  
            'thumbnail_url' => Str::uuid(),
            'hour' => $request->duration,
            'sequence' => $sequence,
            'isDownloadable' => false,
            'video_optimized'=> false,
            'created_at' => Carbon::now(),
        ]); 
 

        if ($request->content_url !== null &&  $contentType=== VIDEO) {
            $filePath = $request->content_url;
            ProcessLessonVideo::dispatch($filePath, $courseContent->id);

            return response()->json([
                'filePath' => $filePath,
                'courseContent' => $courseContent,
                'message' => 'ProcessLessonVideo job dispatched successfully.',
            ]);
        }
    
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

        $contentType = (int) ($request->content_type ?? 0);

        $courseContent = CourseContent::findOrFail($id);
    
        if (!$courseContent) {
            return response()->json([
                'message' => $this->langService->getLang('course_content_not_found'),
            ], 404);
        }
    
        $validationRules = [
            'title' => ['required', 'min:1'],   
            'content_url' => ['nullable'],
        ];
    
        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('courseContent'));
    
        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];
            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        } 

       if ($request->content_url !== null && $request->content_type !== null) {
            // if ($courseContent->content_url) {
            //     Storage::disk('private')->delete($courseContent->content_url);
            // }

            if (($request->content_url !== null &&  $contentType === VIDEO)) {

                $filePath = $request->content_url;
                ProcessLessonVideo::dispatch($filePath, $courseContent->id);
                $courseContent->video_optimized = false;
            }
 
            $courseContent->content_type = $contentType;
            $courseContent->content_url = $request->content_url;
            $courseContent->hour = $request->durarion ?? null;
        }


        $courseContent->title = $request->title;
        $courseContent->updated_at = Carbon::now();
        $courseContent->save();
    
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

    public function getMyEnrolledCourses(Request $request) {
        $user = Auth::user();

        $myTransactions = Transaction::query()
            ->where('customer_id', $user->id)
            ->where('status', TRANSACTION_SUCCESS)
            ->get();
    }

    public function uploadLessonFile(Request $request) {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();
         
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'uploaded_file' => 'required|file',
        ], $this->langService->getLang('courseContent'));

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $fileExtension = null;
        $filePath = null;
        $durarion = null;
        $fileType = null;

        if ($request->hasFile('uploaded_file')) {
            $file = $request->file('uploaded_file');
            $fileExtension = $file->getClientOriginalExtension();
            $filePath = $file->store('/course', 'public');

            switch ($fileExtension) {
                case in_array($fileExtension, VIDEO_EXTENTION):
                    $fileType = VIDEO;
                    $getID3 = new \getID3();
                    $fileInfo = $getID3->analyze($file->getPathname());
                    if (isset($fileInfo['playtime_seconds'])) {
                        $durarion = gmdate("H:i:s", $fileInfo['playtime_seconds']);
                    }

                    $filePath = $request->file('uploaded_file')->store('lesson/video/original', 'private');
                    break;
                case in_array($fileExtension, PDF_EXTENTION):
                    $fileType = PDF;
                    $filePath = $file->store('/course', 'private');
                    break;
                case in_array($fileExtension, IMAGE_EXTENTION):
                    $fileType = IMAGE;
                    break;
                default:
                    dd($fileExtension);
                    return response()->json([
                        'message' => $this->langService->getLang('invalid_file_type'),
                    ], 422);
            }
        } 

        return response()->json([
            'message' => $this->langService->getLang('file_uploaded_successfully'),
             'data' => [
                'content_url' => $filePath,
                'content_type' => $fileType,
                'duration' => $durarion,
            ]
        ]);
    }
    
}
