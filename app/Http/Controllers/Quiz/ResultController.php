<?php

namespace App\Http\Controllers\Quiz;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Quiz\ResultResource;
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
        // Use the authenticated user
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => $this->langService->getLang('unauthenticated'),
            ], 401);
        }
    
        // Ensure the user ID in the request matches the authenticated user
        if (isset($request->user_id) && $user->id != $request->user_id) {
            return response()->json([
                'message' => $this->langService->getLang('user_mismatch'),
            ], 403);
        }
    
        // Validate the input including quiz_id.
        $validationRules = [
            'result'           => 'required|integer',
            'quiz_id'          => 'required|exists:quizzes,id',
            'course_module_id' => 'required|exists:course_modules,id',
            'course_id'        => 'required|exists:courses,id',
        ];
    
        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('Result'));
    
        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }
    
        // Check if a result already exists for this user, quiz, module, and course
        $existingResult = Result::where('user_id', $user->id)
            ->where('q_meta_data_id', $request->quiz_id)
            ->where('course_module_id', $request->course_module_id)
            ->where('course_id', $request->course_id)
            ->first();
    
        if ($existingResult) {
            return response()->json([
                'message' => $this->langService->getLang('result_already_exists'),
                'data'    => new ResultResource($existingResult),
            ], 409); // HTTP 409 Conflict
        }
    
        // Find the quiz using the provided quiz_id from the request
        $quiz = $user->quizzes()->find($request->quiz_id);
    
        if (!$quiz) {
            return response()->json([
                'message' => $this->langService->getLang('quiz_not_found'),
            ], 404);
        }
    
        // Create a new result entry
        $result = Result::create([
            'slug'             => Str::uuid(),
            'result'           => $request->result,
            'user_id'          => $user->id, 
            'q_meta_data_id'   => $quiz->id,
            'course_module_id' => $request->course_module_id,
            'course_id'        => $request->course_id
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
