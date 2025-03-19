<?php

namespace App\Http\Controllers\Quiz;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Quiz\QASection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Quiz\QASectionResource;
use App\Services\LangService;

class QASectionController extends Controller {
    protected $langService;

    public function __construct(LangService $langService) {
        $this->langService = $langService;
    }

    // Optionally, you might want to show all QA Sections (or filter by course_id)
    public function index(Request $request) {
        // For example, fetch all questions for a given course:
        $qaSections = QASection::when($request->course_id, function($query, $courseId) {
            return $query->where('course_id', $courseId);
        })->paginate(10);

        $pagination = $qaSections->toArray();
        unset($pagination['data']);

        return response()->json([
            'pagination' => $pagination,
            'data' => QASectionResource::collection($qaSections),
        ]);
    }

    public function store(Request $request) {
        // Use the authenticated user (any user can ask a question)
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => $this->langService->getLang('unauthenticated'),
            ], 401);
        }
        
        // Validate the input including course_id (which must be provided)
        $validationRules = [
            'question'  => 'required|string',
            'course_id' => 'required|exists:courses,id',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('QASection'));
        
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Create the new QASection (question)
        $newQASection = QASection::create([
            'question'  => $request->question,
            'user_id'   => $user->id,
            'course_id' => $request->course_id, 
        ]);
    
        return response()->json([
            'message' => $this->langService->getLang('qa_section_created_successfully'),
            'data' => new QASectionResource($newQASection),
        ]);
    }
    
    public function show($id) {
        $qaSection = QASection::findOrFail($id);

        return response()->json([
            'data' => new QASectionResource($qaSection),
        ]);
    }

    public function update(Request $request, $id) {
        // Use the authenticated user
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => $this->langService->getLang('unauthenticated'),
            ], 401);
        }

        // Only allow updating if the question belongs to the current user
        $qaSection = QASection::where('user_id', $user->id)->findOrFail($id);

        $validationRules = [
            'question' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('QASection'));

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $qaSection->update($validator->validated());

        return response()->json([
            'message' => $this->langService->getLang('qa_section_updated_successfully'),
            'data' => new QASectionResource($qaSection),
        ]);
    }

    public function destroy($id) {
        // Use the authenticated user
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => $this->langService->getLang('unauthenticated'),
            ], 401);
        }

        // Only allow deletion if the question belongs to the current user
        $qaSection = QASection::where('user_id', $user->id)->findOrFail($id);

        $qaSection->delete();

        return response()->json([
            'message' => $this->langService->getLang('qa_section_deleted_successfully'),
        ]);
    }
}
