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

    public function index() {
        $qaSections = QASection::all()
        ->where('user_id', Auth::id())
        ->paginate(10);
        
        $pagination = $qaSections->toArray();
        unset($pagination['data']);

        return response()->json([
            'pagination' => $pagination,
            'data' => QASectionResource::collection($qaSections),
        ]);
    }
    public function store(Request $request){
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();
            
        if (!$user) 
        return response()->json([
              'message' =>  $this->langService->getLang('user_not_found')
            ]);
    
        $course = $user->courses()->latest()->first(); 
        if (!$course) 
        return response()->json([
           'message' => $this->langService->getLang('course_not_found')
            ], 404);
      
        $validationRules = [
            'question' => 'required|string',
        ];
    
        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('QASection'));
        
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }
            $newQASection = QASection::create([
            'question' => $request->question,
            'user_id' => $user->id,
            'course_id' => $course->id, 
        ]);
    
        return response()->json([
            'message' => $this->langService->getLang('qa_section_created_successfully'),
            'data' => new QASectionResource($newQASection),
        ]);
    }
    
    public function show($id) {
        $qaSection = QASection::where('user_id', Auth::id())->findOrFail($id);

        return response()->json([
            'data' => new QASectionResource($qaSection),
        ]);
    }

    public function update(Request $request, $id) {
        $user = User::all()->first();
        if (!$user) return;

        $qaSection = QASection::where('user_id', $user->id)->findOrFail($id);
        if (!$qaSection) {
            return response()->json([
                'message' => $this->langService->getLang('qa_section_not_found'),
            ], 404);
        }

        $validationRules = [
            'question' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('QASection'));

        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $qaSection->update($validator->validated());

        return response()->json([
            'message' => $this->langService->getLang('qa_section_updated_successfully'),
            'data' => new QASectionResource($qaSection),
        ]);
    }

    public function destroy($id) {
        $user = User::all()->first();
        if (!$user) return;

        $qaSection = QASection::where('user_id', $user->id)->findOrFail($id);

        if (!$qaSection) {
            return response()->json([
                'message' => $this->langService->getLang('qa_section_not_found'),
            ], 404);
        }

        $qaSection->delete();

        return response()->json([
            'message' => $this->langService->getLang('qa_section_deleted_successfully'),
        ]);
    }
}
