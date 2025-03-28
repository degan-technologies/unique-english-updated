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
    
    public function store(Request $request) {
        /**
         * @var User $user
         */
        $user = Auth::user();
        $qaSectionId  = $request->question_id ?? null;
        
        $qaSection = QASection::query()
            ->where('id', $qaSectionId)
            ->first();

        if (!$qaSection) {
            return response()->json([
                'message' => $this->langService->getLang('qa_section_not_found'),
            ], 404);
        }

        $validationRules = [
            'answer'      => 'required|string',
        ];

        $validator = Validator::make($request->all(), $validationRules, (array)$this->langService->getLang('Answer'));


        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $newAnswer = $user->answers()->create([
            'answer'     => $request->answer,
            'question_id'=> $qaSectionId,
            'course_id'  => $qaSection->course_id,
        ]);

        return response()->json([
            'message' => $this->langService->getLang('answer_created_successfully'),
            'data'    => new AnswerResource($newAnswer),
        ]);
    } 
    
    // Update an answer (only by the user who created it)
    public function update(Request $request, $id) {
        $user = Auth::user();
        
        $answer = Answer::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$answer) {
            return response()->json([
                'message' => $this->langService->getLang('answer_not_found'),
            ], 404);
        }

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

        $answer->update([
            'answer' => $request->answer,
        ]);

        return response()->json([
            'message' => $this->langService->getLang('answer_updated_successfully'),
            'data'    => new AnswerResource($answer),
        ]);
    }

    // Delete an answer (only by the user who created it)
    public function destroy($id) {
        $user = Auth::user();

        $answer = Answer::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$answer) {
            return response()->json([
                'message' => $this->langService->getLang('answer_not_found'),
            ], 404);
        }
        
        $answer->delete();

        return response()->json([
            'message' => $this->langService->getLang('answer_deleted_successfully'),
        ]);
    }
}
