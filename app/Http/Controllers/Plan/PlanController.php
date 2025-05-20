<?php

namespace App\Http\Controllers\Plan;

use App\Models\Plan\Plan;
use App\Http\Controllers\Controller;
use App\Http\Resources\Plan\ManagePlanResource;
use Illuminate\Http\Request;
use App\Http\Resources\Plan\PlanResource;
use App\Models\User;
use App\Services\LangService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PlanController extends Controller {
    /**
     * get error traslation and success beased on the language 
     * localized 
     */
    protected $langService;

    public function __construct(LangService $langService) {
        $this->langService = $langService;
    }
    // Fetch all plans
    public function index() {

        $plans = Plan::all();

        return response()->json([
            'data' => PlanResource::collection($plans),
        ]);
    }

    public function getMyPlans() {

        $plans = Plan::all();

        return response()->json([
            'data' => ManagePlanResource::collection($plans),
        ]);
    } 
 
    public function store(Request $request) {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if(!$user){
             return;
        }

        $rules = [
            'name' => 'required|string|unique:plans,name',
            'one_to_one_price' => 'required|numeric|min:0',
            'group_price' => 'required|numeric|min:0',
        ];

        $validator = Validator::make($request->all(), $rules, $this->langService->getLang('plans'));

        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->all()[0],
                'errors' => $validator->errors()
            ], 422);
        }

       $plan = $user->plans()->create([
            'slug' => Str::uuid(),
            'name' => $request->name,
            'price' =>  $request->one_to_one_price,
            'one_to_one_price' => $request->one_to_one_price,
            'group_price' => $request->group_price,
            'duration' => 21,
       ]);

        return response()->json([
            'message' => 'Plan created successfully',
            'data' => new ManagePlanResource($plan),
        ], 201);
    }

    // Fetch a single plan
    public function show($id)
    {
        $plan = Plan::find($id);
        if (!$plan) {
            return response()->json([
                'message' => 'Plan not found'
            ], 404);
        }
        return new ManagePlanResource($plan);
    }

    // Update an existing plan
    public function update(Request $request, $id) {
        $plan = Plan::query()
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if (!$plan) {
            return response()->json([
                'message' => 'Plan not found'
            ], 404);
        }

        $rules = [
            'name' => 'sometimes|string|unique:plans,name,' . $id,
            'one_to_one_price' => 'required|numeric|min:0',
            'group_price' => 'required|numeric|min:0',
        ];

        $validator = Validator::make($request->all(), $rules, $this->langService->getLang('plans'));

        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->all()[0],
                'errors' => $validator->errors()
            ], 422);
        }
        $plan->update([
            'name' => $request->name, 
            'one_to_one_price' => $request->one_to_one_price,
            'group_price' => $request->group_price,
        ]);

        return response()->json([
            'message' => 'Plan updated successfully',
            'data' => new ManagePlanResource($plan),
        ]);
    }

    public function destroy($id) {
        $plan = Plan::query()
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if (!$plan) {
            return response()->json([
                'message' => 'Plan not found'
            ], 404);
        }

        $plan->delete();
        return response()->json([
            'message' => 'Plan deleted successfully'
        ]);
    }
}
