<?php

namespace App\Http\Controllers;

use App\Http\Resources\Comment\FeedBackResource;
use App\Models\Comment\FeedBack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\LangService;
use Illuminate\Support\Facades\Validator;

class FeedBackController extends Controller
{
    protected $langService;

    public function __construct(LangService $langService) {
        $this->langService = $langService;
    }

    /**
     * Display a listing of feedback.
     */
    public function index()
    {
        // Eager-load the "user" relationship to get the reviewer's name.
        $feedbacks = FeedBack::query()
            ->where('instractor_id', Auth::id())
            ->get();

        $review = FeedBack::reviewRate($feedbacks);

        return response()->json([
            'data'             => FeedBackResource::collection($feedbacks),
            'averageRating'    => $review['averageRating'],
            'starDistribution' => $review['starDistribution'],
        ]);
    }

    /**
     * Store a newly created feedback in storage.
     */
    public function store(Request $request)
    {
        $validationRules = [
            'rate'           => 'required|numeric|between:1,5',
            'comment'        => 'required|string',
            'user_id'        => 'required|exists:users,id',
            'instractor_id'  => 'required|exists:users,id',
            'course_id'      => 'required|exists:courses,id',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('feedbacks'));

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors'  => $validator->errors()
            ], 422);
        }

        $feedback = FeedBack::create($request->only(['rate', 'comment', 'user_id', 'instractor_id', 'course_id']));
        $feedback->load('user');

        $transformedFeedback = [
            'id'        => $feedback->id,
            'name'      => $feedback->user ? $feedback->user->name : 'Anonymous',
            'rating'    => $feedback->rate,
            'comment'   => $feedback->comment,
            'timestamp' => $feedback->created_at->toDateTimeString(),
        ];

        return response()->json([
            'message'  => 'Feedback submitted successfully!',
            'feedback' => $transformedFeedback
        ], 201);
    }

    /**
     * Display the specified feedback.
     */
    public function show($id)
    {
        $feedback = FeedBack::with('user')->findOrFail($id);

        $transformedFeedback = [
            'id'        => $feedback->id,
            'name'      => $feedback->user ? $feedback->user->name : 'Anonymous',
            'rating'    => $feedback->rate,
            'comment'   => $feedback->comment,
            'timestamp' => $feedback->created_at->toDateTimeString(),
        ];

        return response()->json($transformedFeedback);
    }

    /**
     * Update the specified feedback in storage.
     */
    public function update(Request $request, $id)
    {
        $feedback = FeedBack::findOrFail($id);

        $validatedData = $request->validate([
            'rate'           => 'sometimes|numeric|between:1,5',
            'comment'        => 'sometimes|string',
            'user_id'        => 'sometimes|exists:users,id',
            'instractor_id'  => 'sometimes|exists:users,id',
            'course_id'      => 'sometimes|exists:courses,id',
        ]);

        $feedback->update($validatedData);
        $feedback->load('user');

        $transformedFeedback = [
            'id'        => $feedback->id,
            'name'      => $feedback->user ? $feedback->user->name : 'Anonymous',
            'rating'    => $feedback->rate,
            'comment'   => $feedback->comment,
            'timestamp' => $feedback->created_at->toDateTimeString(),
        ];

        return response()->json([
            'message'  => 'Feedback updated successfully!',
            'feedback' => $transformedFeedback
        ]);
    }

    /**
     * Remove the specified feedback from storage.
     */
    public function destroy($id)
    {
        $feedback = FeedBack::findOrFail($id);
        $feedback->delete();

        return response()->json(['message' => 'Feedback deleted successfully!']);
    }

    /**
     * Like the specified feedback.
     */
    public function like($id)
    {
        $feedback = FeedBack::findOrFail($id);
        // Assumes a "likes" column exists on the feedback table
        $feedback->increment('likes');

        return response()->json([
            'message' => 'Feedback liked successfully!',
            'likes'   => $feedback->likes,
        ]);
    }

    /**
     * Dislike the specified feedback.
     */
    public function dislike($id)
    {
        $feedback = FeedBack::findOrFail($id);
        // Assumes a "dislikes" column exists on the feedback table
        $feedback->increment('dislikes');

        return response()->json([
            'message'  => 'Feedback disliked successfully!',
            'dislikes' => $feedback->dislikes,
        ]);
    }

    /**
     * Report abuse for the specified feedback.
     *
     * Flagged content is reviewed by staff to determine whether it violates Terms of Service or Community Guidelines.
     * The request must include an issue type and issue details.
     */
    public function report(Request $request, $id)
    {
        $feedback = FeedBack::findOrFail($id);

        $validationRules = [
            'issue_type'    => 'required|string',       // e.g., "Harassment", "Inappropriate Content", etc.
            'issue_details' => 'required|string|min:10', // More details are required to review the report.
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Option 1: If you store report details in the feedback record,
        // you might update specific columns. For example:
        $feedback->increment('reports');
        $feedback->report_issue_type = $request->input('issue_type');
        $feedback->report_issue_details = $request->input('issue_details');
        $feedback->save();

        // Option 2: Alternatively, you might create a separate Report model.

        return response()->json([
            'message' => 'Feedback reported successfully! Our staff will review the flagged content.',
            'reports' => $feedback->reports,
        ]);
    }
}
