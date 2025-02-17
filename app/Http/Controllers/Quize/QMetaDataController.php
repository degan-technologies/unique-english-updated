<?php

namespace App\Http\Controllers\Quize;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Course\Course;
use App\Models\Quize\QMetaData;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Models\Course\CourseContent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Quize\QMetaDataResource;

class QMetaDataController extends Controller{
    protected $langService;

    public function __construct(LangService $langService) {
        $this->langService = $langService;
    }

    public function index(){
        $qMetaData = QMetaData::all()
        ->where('user_id', Auth::id())
        ->paginate(10);
        
        $pagination = $qMetaData->toArray();
        unset($pagination['data']);

        return response()->json([
            'pagination' => $pagination,
            'data' => QMetaDataResource::collection($qMetaData),
        ]);
    }

    public function store(Request $request){
        $user = Auth::user();
        if (!$user) return;
    
        $courseContent = CourseContent::where('user_id', $user->id)->latest()->first();
    
        if (!$courseContent) {
            return response()->json([
                'message' =>$this->langService->getLang('course_content_not_found'),
            ], 404);
        }
    
        $course = Course::where('id', $courseContent->course_id)->first();
    
        if (!$course) {
            return response()->json([
                'message' => $this->langService->getLang('course_not_found'),
            ], 404);
        }
    
        $validationRules = [
            'title' => 'nullable|string|max:255',
            'instraction' => 'nullable|string',
            'question_type' => ['required', 'string', Rule::in(QUESTION_TYPES)],
        ];
    
        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('QMetaData'));
    
        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }
    
        $qMetaData = QMetaData::create([
            'slug' => Str::uuid(),
            'question_type' => $request->question_type,
            'instraction' => $request->instraction,
            'title' => $request->title,
            'user_id' => $user->id, 
            'course_content_id' => $courseContent->id, 
            'course_id' => $course->id, 
        ]);
    
        return response()->json([
            'message' => $this->langService->getLang('q_meta_data_created_successfully'),
            'data' => new QMetaDataResource($qMetaData),
        ]);
    }

    public function show($id){
        $qMetaData = QMetaData::query()
        ->where('user_id', Auth::id())
        ->first();
        
        return response()->json([
            'data' => new QMetaDataResource($qMetaData),
        ]);
    }

    public function update(Request $request, $id){
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();
        if (!$user) return;

        $qMetaData = QMetaData::where('user_id', $user->id)->findOrFail($id);

        $validationRules = [
            'title' => 'nullable|string|max:255',
            'instraction' => 'nullable|string',
            'question_type' => ['required', 'string', Rule::in(QUESTION_TYPES)],
        ];
        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('QMetaData'));

        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $qMetaData->update($validator->validated());

        return response()->json([
            'message' => $this->langService->getLang('q_meta_data_updated_successfully'),
            'data' => new QMetaDataResource($qMetaData),
        ]);
    }

    public function destroy($id) {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();
        if (!$user) return;

        $qMetaData = QMetaData::where('user_id', $user->id)->findOrFail($id);

        $qMetaData->delete();

        return response()->json([
            'message' => $this->langService->getLang('q_meta_data_deleted_successfully'),
        ]);
    }
}