<?php

namespace App\Http\Controllers\Live;

use Illuminate\Http\Request;
use App\Services\LangService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Live\VirtualClassEnrollment;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Live\VirtualClassEnrollmentResource;

class VirtualClassEnrollmentController extends Controller {
    protected $langService;

    public function __construct(LangService $langService)
    {
        $this->langService = $langService;
    }

   
    public function index()
    {
        $enrollments = VirtualClassEnrollment::all()
            ->where('user_id', Auth::id())
            ->paginate(10);

        $pagination = $enrollments->toArray();
        unset($pagination['data']);

        return response()->json([
            'pagination' => $pagination,
            'data' => VirtualClassEnrollmentResource::collection($enrollments),
        ]);
    }
    public function store(Request $request)
    {
        $user = User::query()
            ->first();
    
        if (!$user) {
            return response()->json([
                'message' =>$this->langService->getLang('Unauthorized_to_Create_VirtualClassEnrollment.',)
            ], 403);
        }
    
        $validationRules = [
            'enrolled_at' => 'required|date',
            'price_plan' => 'required|string|max:255',
            'remaining_date' => 'required|integer|min:0',
            'end_date' => 'required|date|after:enrolled_at',
        ];
    
        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('VertualClassEnrollment'));
    
        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];
    
            return response()->json([
                'message' => $message,
                'errors' => $validator->errors(),
            ], 422);
        }
    
        $enrollment = $user->enrollments()->create([
            'slug' => Str::uuid(),
            'enrolled_at' => $request->enrolled_at,
            'price_plan' => $request->price_plan,
            'remaining_date' => $request->remaining_date,
            'instructor_id' => $user->id, 
            'end_date' => $request->end_date,
            'user_id' =>  $user->id,
        ]);
    
        return response()->json([
            'message' => $this->langService->getLang('VertualClassEnrollment_created_successfully'),
            'data' => new VirtualClassEnrollmentResource($enrollment),
        ]);
    }
    
    public function show($id){

        $enrollment = VirtualClassEnrollment::query()
        ->where('user_id', Auth::id())
        ->first();

    return response()->json([
        'data' => new  VirtualClassEnrollmentResource($enrollment)
    ]); }

    
    public function update(Request $request, $id)
    {
        $user = User::query()
            ->first();
    
        if (!$user) {
            return response()->json([
                'message' =>$this->langService->getLang('Unauthorized_to_Create_VirtualClassEnrollment.',)
            ], 403);
        }
    
        $enrollment = VirtualClassEnrollment::query()
            ->where('user_id', $user->id)
            ->findOrFail($id);
    
        if (!$enrollment) {
            return response()->json([
                'message' => $this->langService->getLang('VertualClassEnrollment_not_found'),
            ], 404);
        }
    
        $validationRules = [
            'enrolled_at' => 'required|date',
            'price_plan' => 'required|string|max:255',
            'remaining_date' => 'required|integer|min:0',
            'end_date' => 'required|date|after:enrolled_at',
        ];
    
        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('VertualClassEnrollment'));
    
        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->all()[0],
                'errors' => $validator->errors(),
            ], 422);
        }
    
        $validatedData = $validator->validated();
        $validatedData['instructor_id'] = $user->id; 
    
        $enrollment->update($validatedData);
    
        return response()->json(new VirtualClassEnrollmentResource($enrollment));
    }
  
    public function destroy($id){

        $user = User::query()
        ->whereSystemAdminOrInstructor()
        ->first();

    $enrollment = VirtualClassEnrollment::query()
        ->where('user_id', $user->id)
        ->findOrFail($id);

    if(!$enrollment) {
        return response()->json([
            'message' => $this->langService->getLang('VirtualClassEnrollment_not_found'),
        ], 404);
    }

        $enrollment->delete();

        return response()->json(['message' => $this->langService->getLang('VirtualClassEnrollment_deleted_successfully')]);
    }
}
