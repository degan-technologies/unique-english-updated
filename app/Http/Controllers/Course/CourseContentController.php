<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Http\Resources\Course\CourseContentResource;
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

        $fileExtension = null;
        $filePath = null;    
        $durarion = null;
        $moduleId = $request->course_module_id ?? null;
        $fileType = null;
        $imagePath = null;
    
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
            'content_url' => 'nullable|file|mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime,video/3gpp,video/mov,video/x-msvideo,video/x-ms-wmv,video/webm,video/ogg,video/x-flv',
            'thumbnail_url' => 'image', 
        ];
    
        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('courseContent'));
    
        if (!$validator->passes()) {
            return response()->json([
            'message' => $validator->errors()->all()[0],
            'errors' => $validator->errors()
            ], 422);
        }

        if($request->hasFile('content_url')) {
             $fileExtension = $request->file('content_url')->getClientOriginalExtension();
             $filePath = $request->file('content_url')->store('/course', 'public');

             if (in_array($fileExtension, VIDEO_EXTENTION)) {
                $fileType = VIDEO;
                $fileFullPath = storage_path('app/public/' . $filePath);
                $getID3 = new \getID3();
                $fileInfo = $getID3->analyze($fileFullPath);
                if (isset($fileInfo['playtime_seconds'])) {
                    $durarion = gmdate("H:i:s", $fileInfo['playtime_seconds']);
                }
             }

            if (in_array($fileExtension, PDF_EXTENTION)) {
                $fileType = PDF;
            }

            if (in_array($fileExtension, IMAGE_EXTENTION)) {
                $fileType = IMAGE;
            }
        } 

        if($request->hasFile('thumbnail_url')) {
            $imagePath = $request->file('thumbnail_url')->store('/course', 'public');
        }
    
        $courseContent = $user->courseContents()->create([
            'course_module_id' => $moduleId,  
            'course_id' => $request->course_id,
            'slug' => Str::uuid(),
            'title' => $request->title,
            'description' => $request->description,
            'content_type' => $fileType,
            'content_url' => $filePath,
            'thumbnail_url' => $imagePath,
            'hour' => $durarion,
            'status' => PUBLISHED,
            'sequence' => $sequence,
            'isDownloadable' => false,
            'created_at' => Carbon::now(),
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

        $fileExtension = null;
        $filePath = null;
        $durarion = null; 
        $fileType = null;
        $imagePath = null;

        $courseContent = CourseContent::findOrFail($id);
    
        if (!$courseContent) {
            return response()->json([
                'message' => $this->langService->getLang('course_content_not_found'),
            ], 404);
        }
    
        $validationRules = [
            'title' => ['required', 'not_regex:/[\\\\\/\?\%\*\:\|\"<>]/', 'min:4'],
            'description' => 'min:10', 
            'content_url' =>'nullable|file|mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime,video/3gpp,video/mov,video/x-msvideo,video/x-ms-wmv,video/webm,video/ogg,video/x-flv',
            'thumbnail_url' => 'image',   
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

        if ($request->hasFile('content_url')) {
            $fileExtension = $request->file('content_url')->getClientOriginalExtension();
            $filePath = $request->file('content_url')->store('/course', 'public');

            if (in_array($fileExtension, VIDEO_EXTENTION)) {
                $fileType = VIDEO;
                $fileFullPath = storage_path('app/public/' . $filePath);
                $getID3 = new \getID3();
                $fileInfo = $getID3->analyze($fileFullPath);
                if (isset($fileInfo['playtime_seconds'])) {
                    $durarion = gmdate("H:i:s", $fileInfo['playtime_seconds']);
                }
            }

            if (in_array($fileExtension, PDF_EXTENTION)) {
                $fileType = PDF;
            }

            if (in_array($fileExtension, IMAGE_EXTENTION)) {
                $fileType = IMAGE;
            }
        }

        if ($request->hasFile('thumbnail_url')) {
            $imagePath = $request->file('thumbnail_url')->store('/course', 'public');
        }

        $courseContent->update([   
            'title' => $request->title,
            'description' => $request->description,
            'content_type' => $fileType,
            'content_url' => $filePath,
            'thumbnail_url' => $imagePath,
            'hour' => $durarion, 
            'updated_at' => Carbon::now(),
        ]);
    
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
    
}
