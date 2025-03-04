<?php

namespace App\Http\Controllers\Quiz;

use App\Services\LangService;
use App\Models\User;
use App\Models\Quiz\Quiz;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\Quiz\QMetaData;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Quiz\QuizResource;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class QuizController extends Controller
{
    protected $langService;

    public function __construct(LangService $langService)
    {
        $this->langService = $langService;
    }

    public function index()
    {
        $quizzes = Quiz::where('user_id', Auth::id())->get();

        return response()->json([
            'data' => QuizResource::collection($quizzes),
        ]);
    }

    public function store(Request $request)
    {
        $qMetaDataId = $request->exam_id ?? null;

        $user = User::query()
                ->whereSystemAdminOrInstructor()
                ->first();
            
        if (!$user) return;

        $qMetaData = QMetaData::query()
                    ->where('user_id', $user->id)
                    ->where('id', $qMetaDataId)
                    ->first();

        if (!$qMetaData) {
            return response()->json(['message' => $this->langService->getLang('q_meta_data_not_found')], 404);
        }
 
        $validationRules = [
            'question_type' => ['required', 'string', Rule::in(QUESTION_TYPES)],
            'question'      => 'required|string',
            'choice'        => 'required|array',
            'answer'        => 'required|string', 
            'hint' => 'string|min:10', 
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('quiz'));

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $quiz = $user->quizzes()->create([
            'slug'           => Str::uuid(),
            'question_type'  => $request->question_type,
            'question'       => $request->question,
            'choice'         => $request->choice, 
            'answer'         => $request->answer, 
            'q_meta_data_id' => $qMetaDataId,
            'hint'           => $request->hint,
        ]);

        return response()->json([
            'message' => $this->langService->getLang('quiz_created_successfully'),
            'data'    => new QuizResource($quiz),
        ]);
    }

    public function show($id)
    {
        $quiz = Quiz::where('user_id', Auth::id())->findOrFail($id);

        return response()->json(['data' => new QuizResource($quiz)]);
    }

    public function update(Request $request, $id)
    {
       $user = User::query()
                ->whereSystemAdminOrInstructor()
                ->first();
            
        if(!$user) return;

         $quiz = Quiz::query()
                ->where('user_id', $user->id)
                ->where('id', $id)
                ->first();

        if(!$quiz) {
            return response()->json([
                'message' => $this->langService->getLang('quiz_not_found'),
            ], 404);
        }

        $validationRules = [
            'question'      => 'required|string',
            'choice'        => 'required|array',
            'answer'        => 'required|string', 
             'hint' => 'string|min:10', 
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('quiz'));

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }
        
        $quiz->update([
            'question'      => $request->question,
            'choice'        => $request->choice,
            'answer'        => $request->answer,
            'hint'          => $request->hint,
        ]);

        return response()->json([
            'message' => $this->langService->getLang('quiz_updated_successfully'),
            'data'    => new QuizResource($quiz),
        ]);
    }

    public function destroy($id)
    {
        $quiz = Quiz::find($id);

        if (!$quiz) {
            return response()->json(['message' => 'Quiz not found'], 404);
        }

        // Delete the quiz
        $quiz->delete();
        return response()->json(['message' => 'Quiz deleted successfully']);
    }
}
