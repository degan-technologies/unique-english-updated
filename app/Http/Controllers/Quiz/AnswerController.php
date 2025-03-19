<?php

namespace App\Http\Controllers\Quiz;

use App\Models\User;
use App\Models\Quiz\Answer;
use App\Models\Quiz\QASection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Quiz\AnswerResource;
use App\Services\LangService;

class AnswerController extends Controller {
    protected $langService;

    public function __construct(LangService $langService) {
        $this->langService = $langService;
    }

    // Fetch answers for a specific QA Section or course
    public function index(Request $request) {
        $answers = Answer::when($request->question_id, function($query, $questionId) {
                return $query->where('question_id', $questionId);
            })
            ->when($request->course_id, function($query, $courseId) {
                return $query->where('course_id', $courseId);
            })
            ->paginate(10);
    
        $pagination = $answers->toArray();
        unset($pagination['data']);
    
        return response()->json([
            'pagination' => $pagination,
            'data' => AnswerResource::collection($answers),
        ]);
    }
    

    // Store a new answer for a question
    public function store(Request $request) {
        // Use the authenticated user
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => $this->langService->getLang('unauthenticated'),
            ], 401);
        }

        // Validate the input, including question_id and course_id
        $validationRules = [
            'answer'      => 'required|string',
            'question_id' => 'required|exists:q_a_sections,id',  // Reference to the question
            'course_id'   => 'required|exists:courses,id',  // Reference to the course
        ];

        $validator = Validator::make($request->all(), $validationRules, (array)$this->langService->getLang('Answer'));


        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Create the new Answer
        $newAnswer = Answer::create([
            'answer'     => $request->answer,
            'user_id'    => $user->id,
            'question_id'=> $request->question_id,
            'course_id'  => $request->course_id,
        ]);

        return response()->json([
            'message' => $this->langService->getLang('answer_created_successfully'),
            'data'    => new AnswerResource($newAnswer),
        ]);
    }

    // Show a specific answer
    public function show($id) {
        $answer = Answer::findOrFail($id);

        return response()->json([
            'data' => new AnswerResource($answer),
        ]);
    }

    // Update an answer (only by the user who created it)
    public function update(Request $request, $id) {
        // Use the authenticated user
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => $this->langService->getLang('unauthenticated'),
            ], 401);
        }

        // Only allow updating if the answer belongs to the current user
        $answer = Answer::where('user_id', $user->id)->findOrFail($id);

        $validationRules = [
            'answer' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $validationRules, (array)$this->langService->getLang('Answer'));


        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $answer->update($validator->validated());

        return response()->json([
            'message' => $this->langService->getLang('answer_updated_successfully'),
            'data'    => new AnswerResource($answer),
        ]);
    }

    // Delete an answer (only by the user who created it)
    public function destroy($id) {
        // Use the authenticated user
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => $this->langService->getLang('unauthenticated'),
            ], 401);
        }

        // Only allow deletion if the answer belongs to the current user
        $answer = Answer::where('user_id', $user->id)->findOrFail($id);

        $answer->delete();

        return response()->json([
            'message' => $this->langService->getLang('answer_deleted_successfully'),
        ]);
    }
}
