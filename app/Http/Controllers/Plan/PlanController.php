<?php

namespace App\Http\Controllers\Plan;

use App\Models\Plan\Plan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Plan\PlanResource;
use Illuminate\Support\Facades\Validator;

class PlanController extends Controller
{
    // Fetch all plans
    public function index()
    {
        return response()->json([
            'data' => PlanResource::collection(Plan::all()),
        ]);
    }

    // Store a new plan
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:plans,name',
            'price' => 'required|integer|min:0',
            'duration' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $plan = Plan::create($request->all());

        return response()->json([
            'message' => 'Plan created successfully',
            'data' => new PlanResource($plan),
        ], 201);
    }

    // Fetch a single plan
    public function show($id)
    {
        $plan = Plan::find($id);
        if (!$plan) {
            return response()->json(['message' => 'Plan not found'], 404);
        }
        return new PlanResource($plan);
    }

    // Update an existing plan
    public function update(Request $request, $id)
    {
        $plan = Plan::find($id);
        if (!$plan) {
            return response()->json(['message' => 'Plan not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|unique:plans,name,' . $id,
            'price' => 'sometimes|integer|min:0',
            'duration' => 'sometimes|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $plan->update($request->all());

        return response()->json([
            'message' => 'Plan updated successfully',
            'data' => new PlanResource($plan),
        ]);
    }

    // Delete a plan
    public function destroy($id)
    {
        $plan = Plan::find($id);
        if (!$plan) {
            return response()->json(['message' => 'Plan not found'], 404);
        }

        $plan->delete();
        return response()->json(['message' => 'Plan deleted successfully']);
    }
}
