<?php 

namespace App\Http\Controllers\Quiz;

use App\Models\User;
use Illuminate\Support\Str; 
use App\Services\LangService;
use Illuminate\Http\Request; 
use App\Models\Quiz\QMetaData; 
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Quiz\QMetaDataResource;
use App\Http\Resources\Quiz\QuizResource;
use App\Models\Course\Course;
use App\Models\Course\CourseModule;
use App\Models\Quiz\Quiz;
class QMetaDataController extends Controller{
    protected $langService;

    public function __construct(LangService $langService) {
        $this->langService = $langService;
    }

    public function index(){
        $qMetaData = QMetaData::where('user_id', Auth::id())->paginate(10); // Fixed paginate issue

        $pagination = $qMetaData->toArray();
        unset($pagination['data']);

        return response()->json([
            'pagination' => $pagination,
            'data' => QMetaDataResource::collection($qMetaData),
        ]);
    }

    public function fetchInstructorExam(Request $request){
        /**
         * @var mixed $qMetaData
         */
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if(!$user) return;

        $moduleId = $request->module_id?? null;

        $courseModule = CourseModule::query()
            ->where('user_id', $user->id)
            ->find($moduleId);

        if (!$courseModule) {
            return response()->json([
               'message' => $this->langService->getLang('course_module_not_found'),
            ], 404);
        }

        $qMetaData = QMetaData::query()   
            ->where('course_module_id', $moduleId)
            ->where('user_id', Auth::id())
            ->paginate(10);

        $pagination = $qMetaData->toArray();
        unset($pagination['data']);

        if($qMetaData->isEmpty()){
            return response()->json([
                'message' => $this->langService->getLang('no_data_found'),
            ], 404);
        }

        return response()->json([
            'pagination'=> $pagination,
            'data'=> QMetaDataResource::collection($qMetaData),
        ]);
    }

    public function fetchStudentExam(Request $request) {
        /**
         * @var mixed $qMetaData
         */
        $user = Auth::user();

        if (!$user) return;

        $moduleId = $request->module_id ?? null;

        $courseModule = CourseModule::query() 
            ->find($moduleId);

        $eligibleCourse = Course::checkEligibility($courseModule->course_id);

        if (!$eligibleCourse) {
            return response()->json([
                'message' => $this->langService->getLang('course_not_found'),
            ], 404);
        }

        $qMetaData = QMetaData::query()
            ->where('course_module_id', $moduleId) 
            ->get();
 

        if ($qMetaData->isEmpty()) {
            return response()->json([
                'message' => $this->langService->getLang('no_data_found'),
            ], 404);
        }

        return response()->json([ 
            'data' => QMetaDataResource::collection($qMetaData),
        ]);
    }


    public function store(Request $request){
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if(!$user) return;
        
        $moduleId = $request->module_id ?? null; 

        $validationRules = [
            'title' => 'required|string|min:5|max:255',
            'instruction' => 'string|min:10',
        ];
    
        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('QMetaData'));
    
        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $courseModule = CourseModule::query()
            ->where('user_id', $user->id)
            ->find($moduleId);

        if (!$courseModule) {
            return response()->json([
               'message' => $this->langService->getLang('course_module_not_found'),
            ], 404);
        }

        $qMetaData = $user->qMetaDatas()->create([ 
            'slug' => Str::uuid(), 
            'instruction' => $request->instruction,
            'title' => $request->title,  
            'course_module_id' => $moduleId, 
            'course_id' => $courseModule->course_id, 
        ]);

        return response()->json([
            'message' => $this->langService->getLang('q_meta_data_created_successfully'),
            'data' => new QMetaDataResource($qMetaData),
        ]);
    }

    public function show($id){
        $qMetaData = QMetaData::where('user_id', Auth::id())->findOrFail($id); // Fixed missing findOrFail

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
            'title' => 'required|string|min:5|max:255',
            'instruction' => 'string|min:10',
        ];
        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('QMetaData'));

        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $qMetaData->update([
            'title' => $request->title,
            'instruction' => $request->instruction,
        ]);

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

    public function checkAnswer($qmId) {
        $user = Auth::user();

        $quiz = Quiz::query()
            ->where('q_meta_data_id', $qmId)
            ->get();
 
        return response()->json([
            'data' => QuizResource::collection($quiz),
        ]);
    }
}
