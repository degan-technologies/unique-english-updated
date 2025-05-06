<?php

namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use App\Http\Resources\Test\TestResource;
use App\Models\Test\Test;
use Illuminate\Http\Request;
use App\Services\LangService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TestController extends Controller
{
    protected $langService;

    public function __construct(LangService $langService)
    {
        $this->langService = $langService;
    }

    public function index()
    {
        
        $tests = Test::all();
        return response()->json(['data' => TestResource::collection($tests)]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string',
            'choices' => 'required|array',
            'answer' => 'required|array',
        ], [
            'question.required' => 'The question field is required.',
            'question.string' => 'The question must be a string.',
            'choices.required' => 'Choices are required.',
            'choices.array' => 'Choices must be an array.',
            'answer.required' => 'Answer is required.',
            'answer.array' => 'Answer must be an array.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $test = Test::create([
            'question' => $request->question,
            'choices' => $request->choices,
            'answer' => $request->answer,
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'message' => $this->langService->getLang('test_created_successfully'),
            'data' => new TestResource($test),
        ]);
    }

    public function show($id)
    {
        try {
            $test = Test::where('user_id', Auth::id())->findOrFail($id);
            return response()->json(['data' => new TestResource($test)]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $this->langService->getLang('test_not_found')], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $test = Test::where('user_id', Auth::id())->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $this->langService->getLang('test_not_found')], 404);
        }

        $validator = Validator::make($request->all(), [
            'question' => 'required|string',
            'choices' => 'required|array',
            'answer' => 'required|array',
        ], [
            'question.required' => 'The question field is required.',
            'question.string' => 'The question must be a string.',
            'choices.required' => 'Choices are required.',
            'choices.array' => 'Choices must be an array.',
            'answer.required' => 'Answer is required.',
            'answer.array' => 'Answer must be an array.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $test->update([
            'question' => $request->question,
            'choices' => $request->choices,
            'answer' => $request->answer,
        ]);

        return response()->json([
            'message' => $this->langService->getLang('test_updated_successfully'),
            'data' => new TestResource($test),
        ]);
    }

    public function destroy($id)
    {
        try {
            $test = Test::where('user_id', Auth::id())->findOrFail($id);
            $test->delete();
            return response()->json(['message' => $this->langService->getLang('test_deleted_successfully')]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => __('errors.test_not_found')], 404);
        }
    }
}
