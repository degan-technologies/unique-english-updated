<?php

namespace App\Http\Controllers\Quiz;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Quiz\ResultResource;
use App\Models\Course\Course;
use App\Models\Quiz\Result;
use App\Services\LangService;

class ResultController extends Controller {
    protected $langService;

    public function __construct(LangService $langService) {
        $this->langService = $langService;
    }

    public function index() {
        $results = Result::query()
            ->where('user_id', Auth::id())
            ->paginate(10);
 
        $pagination = $results->toArray();
        unset($pagination['data']);

        return response()->json([
            'pagination' => $pagination,
            'data' => ResultResource::collection($results),
        ]);
    }

    public function store(Request $request) { 

        /**
         * @var User $user
         */

        $user = Auth::user();
        $examId = $request->exam_id;

        if (!$user) {
            return response()->json([
                'message' => $this->langService->getLang('unauthenticated'),
            ], 401);
        }

        $exam = $user->qMetaDatas()->find($examId);

        if (!$exam) {
            return response()->json([
                'message' => $this->langService->getLang('exam_not_found'),
            ], 404);
        }

        $checkEligibility = Course::checkEligibility($exam->course_id,);

        if(!$checkEligibility) {
            return response()->json([
                'message' => $this->langService->getLang('not_eligible_for_exam'),
            ], 403);
        }
    
        $validationRules = [
            'result'           => 'required|integer',
        ];
    
        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('Result'));
    
        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $checkResult = $user->results()->where('q_meta_data_id', $examId)->first();

        if ($checkResult) {
            $checkResult->update([
                'result'=> $request->result,
            ]);

            return response()->json([
                'message' => $this->langService->getLang('result_created_successfully'),
                'data'    => new ResultResource($checkResult),
            ]);
        }
    
        $result = $user->results()->create([
            'slug'             => Str::uuid(),
            'result'           => $request->result, 
            'q_meta_data_id'   => $examId, 
        ]);
    
        return response()->json([
            'message' => $this->langService->getLang('result_created_successfully'),
            'data'    => new ResultResource($result),
        ]);
    }
    
    
    
    public function show($id){
        $result = Result::query()
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json([
            'data' => new ResultResource($result),
        ]);
    }

    public function update(Request $request, $id){
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();
        if (!$user) return;

        $result = Result::query()
            ->where('user_id', $user->id)
            ->findOrFail($id);

        if (!$result) {
            return response()->json([
                'message' => $this->langService->getLang('result_not_found'),
            ], 404);
        }

        $validationRules = [
            'result' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('Result'));

        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $result->update($validator->validated());

        return response()->json([
            'message' => $this->langService->getLang('result_updated_successfully'),
            'data' => new ResultResource($result),
        ]);
    }

    public function destroy($id)
    {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();
        if (!$user) return;

        $result = Result::query()
            ->where('user_id', $user->id)
            ->findOrFail($id);

        if (!$result) {
            return response()->json([
                'message' => $this->langService->getLang('result_not_found'),
            ], 404);
        }

        $result->delete();

        return response()->json([
            'message' => $this->langService->getLang('result_deleted_successfully'),
        ]);
    }
}
