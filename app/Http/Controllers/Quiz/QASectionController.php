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
use App\Models\Course\Course;
use App\Services\LangService;

class QASectionController extends Controller {
    protected $langService;

    public function __construct(LangService $langService) {
        $this->langService = $langService;
    }
 
    public function index(Request $request) { 
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

        /**
         * @var User $user
         */
        $user = Auth::user();
        $courseId = $request->course_id ?? null;

        $course =Course::find($courseId);

        if (!$course) {
            return response()->json([
                'message' => $this->langService->getLang('course_not_found'),
            ], 404);
        }
        
        $validationRules = [
            'question'  => 'required|string',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('QASection'));
        
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $newQASection = $user->qaSections()->create([
            'question'  => $request->question,
            'course_id' => $courseId, 
        ]);
    
        return response()->json([
            'message' => $this->langService->getLang('qa_section_created_successfully'),
            'data' => new QASectionResource($newQASection),
        ]);
    }
    
    public function show($id) {

        $user = Auth::user(); 

        $qaSection = QASection::query()
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        return response()->json([
            'data' => new QASectionResource($qaSection),
        ]);
    }

    public function update(Request $request, $id) {
        // Use the authenticated user
        $user = Auth::user();

        $qaSection = QASection::query()
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if(!$qaSection) {
            return response()->json([
                'message' => $this->langService->getLang('qa_section_not_found'),
            ], 404);
        }

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
