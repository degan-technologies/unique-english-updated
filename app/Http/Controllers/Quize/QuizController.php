<?php

namespace App\Http\Controllers\Quize;

use App\Models\User;
use App\Models\Quiz\Quiz;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Quiz\QMetaData;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Quize\QuizResource;

class QuizController extends Controller {
    protected $langService;

    public function __construct(LangService $langService) {
        $this->langService = $langService; }
    public function index(){
        $quizzes = Quiz::all()
            ->where('user_id', Auth::id())
            ->paginate(10);

        $pagination = $quizzes->toArray();
        unset($pagination['data']);

        return response()->json([
            'pagination' => $pagination,
            'data' => QuizResource::collection($quizzes),
        ]);
    }
    public function store(Request $request){
        $user = User::query()
        ->whereSystemAdminOrInstructor()
        ->first();
        if (!$user) return;
    
        $validationRules = [
            'question_type' => ['required', 'string', Rule::in(QUESTION_TYPES)],
            'question' => 'required|string',
            'choice' => 'nullable|array',
            'answer' => 'required|array',
        ];
    
        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('Quize'));
    
        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }
    
        $qMetaData = QMetaData::where('user_id', Auth::id())->latest()->first();
    
        if (!$qMetaData) {
            return response()->json([
                'message' => $this->langService->getLang('q_meta_data_not_found'),
            ], 404);
        }
    
        $quiz = Quiz::create([
            'slug' => Str::uuid(),
            'question_type' => $request->question_type,
            'question' => $request->question,
            'choice' => $request->choice,
            'answer' => $request->answer,
            'q_meta_data_id' => $qMetaData->id, 
            'user_id' => Auth::id(),
        ]);
    
        return response()->json([
            'message' => $this->langService->getLang('quiz_created_successfully'),
            'data' => new QuizResource($quiz),
        ]);
    }
    
    public function show($id) {
        $quiz = Quiz::query()
        -> where('user_id', Auth::id())
        ->first();

        return response()->json([
            'data' => new QuizResource($quiz),
        ]);
    }
    public function update(Request $request, $id){
        
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();
            if (!$user) return;
    
        $quiz = Quiz::query()
        ->where('user_id', $user->id)
        ->findOrFail($id);

        if (!$quiz) {
            return response()->json([
                'message' => $this->langService->getLang('quiz_not_found'),
            ], 404);
        }
        $validationRules = [
            'question_type' => ['required',Rule::in(QUESTION_TYPES)],
            'question' => 'required|string',
            'choice' => 'nullable|array',
            'answer' => 'required|array',
        ];
    
        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('Quize'));

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];
            return response()->json([
                'message' => $message,
                'errors' => $validator->errors(),
            ], 422);
        }

        $quiz->update($validator->validated());

        return response()->json([
            'message' => $this->langService->getLang('quiz_updated_successfully'),
            'data' => new QuizResource($quiz),
        ]);
    }
    public function destroy($id){

        $user = User::query()
        ->whereSystemAdminOrInstructor()
        ->first();
        if (!$user) return;

        $quiz = Quiz::query()
        ->where('user_id',  $user->id)
        ->findOrFail($id);

        if(!$quiz) {
            return response()->json([
                'message' => $this->langService->getLang('quiz_not_found'),
            ], 404);
        }
        $quiz->delete();

        return response()->json([
            'message' => $this->langService->getLang('quiz_deleted_successfully'),
        ]);
    }
}
