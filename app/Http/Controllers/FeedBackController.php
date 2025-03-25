<?php

namespace App\Http\Controllers;

use App\Http\Resources\Comment\FeedBackResource;
use App\Models\Book\Book;
use App\Models\Comment\FeedBack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\LangService;
use Illuminate\Support\Facades\Validator;
use App\Models\Comment\FeedbackUserInteraction;
use App\Models\Course\Course;
use App\Models\User;
use Illuminate\Validation\Rule;

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
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();
        if(!$user) return;
        // Eager-load the "user" relationship to get the reviewer's name.
        $feedbacks = FeedBack::query()
            ->where('instractor_id', $user->id)
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
    public function store(Request $request) {
        $feedbackType = $request->feedbackType ?? null;
        $courseSlug = $request->slug ?? null;
        $currentFeedback = null;
        $eligibleCourse = false;
        $courseId = null;
        $bookId = null;

        /* @var \App\Models\User $user
        */
        $user = Auth::user();

        $feedbackCompleted = $user->feedBacks;

        switch ($feedbackType) {
            case 'course':
                $currentFeedback = Course::query()
                    ->where('slug', $courseSlug)
                    ->first();
                $courseId = $currentFeedback->id;
                $eligibleCourse = Course::checkEligibility($currentFeedback->id);
                break;
            case 'book':
                $currentFeedback = Book::query()
                    ->where('slug', $courseSlug)
                    ->first();
                $bookId = $currentFeedback->id;
                $eligibleCourse = Book::checkEligibility($currentFeedback->id);
                break;
            default:
                return response()->json([
                    'message' => $this->langService->getLang('invalid_feedback_type')
                ], 400);
        }

        $feedbackCompleted = FeedBack::query()
            ->where('user_id', $user->id)
            ->where(function ($query) use ($courseId, $bookId) {
                $query->orWhere('course_id', $courseId);
                $query->orWhere('book_id', $bookId);
            })
            ->first();

        if($feedbackCompleted){
            return response()->json([
                'message' => $this->langService->getLang('feedback_already_submitted')
            ], 403);
        }

        if(!$currentFeedback) {
            return response()->json([
                'message' => $this->langService->getLang('course_not_found')
            ], 404);
        }


        if (!$eligibleCourse) {
            return response()->json([
                'message' => $this->langService->getLang('unauthorized_action')
            ], 403);
        }

        $validationRules = [
            'rate'           => 'required|numeric|between:1,5',
            'comment'        => 'required|string',
        ];
        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('feedbacks'));

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];
            return response()->json([
                'message' => $message,
                'errors'  => $validator->errors()
            ], 422);
        }

        $feedback = $user->feedBacks()->create([
            'rate' => $request->rate,
            'comment' => $request->comment,
            'course_id'=>  $courseId,
            'instractor_id' => $currentFeedback->user_id,
            'book_id' => $bookId,
        ]);

        return response()->json([
            'message'  => 'Feedback submitted successfully!',
            'data' => new FeedBackResource($feedback),
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
            'name'      => $feedback->user,
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
    public function addFavorite(Request $request, $id) {
        /* @var \App\Models\User $user
        */
        $user = Auth::user();
        $favorite = FeedbackUserInteraction::query()
        ->where('user_id', $user->id)
        ->where('feed_back_id', $id)
        ->first();
        $validationRules = [
            'action'           => ['required', Rule::in(FEEDBACK_ACTIONS)],
        ];
        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('feedbacks'));
        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];
            return response()->json([
                'message' => $message,
                'errors'  => $validator->errors()
            ], 422);
        }
        if(!$favorite) {
            $user->feedbackUserInteractions()->create([
                'favorite' => $request->action,
                'feed_back_id' => $id,
            ]);
        }
        $favorite->update([
            'favorite' => $request->action,
        ]);
        $reaction = FeedbackUserInteraction::query()
            ->where('feed_back_id', $id)
            ->get();
        $countLike = $reaction ->where('favorite', 'liked')->count();
        $countDislike = $reaction ->where('favorite', 'disliked')->count();
        return response()->json([
            'like' => $countLike,
            'dislike' => $countDislike,
        ]);
    }
    /**
     * Dislike the specified feedback.
     */
    public function dislike($id) {
        $userId = Auth::id();
        $feedback = FeedBack::findOrFail($id);
        $interaction = FeedbackUserInteraction::firstOrNew(
            ['user_id' => $userId, 'feedback_id' => $id]
        );
        if ($interaction->disliked) {
            return response()->json(['message' => 'You have already disliked this feedback.'], 400);
        }
        $interaction->disliked = true;
        $interaction->liked = false; // Remove like if previously liked
        $interaction->save();
        $feedback->increment('dislikes');
        if ($feedback->likes > 0) {
            $feedback->decrement('likes'); // Adjust count if previously liked
        }
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
    public function report(Request $request, $id) {
        $userId = Auth::id();
        $feedback = FeedBack::findOrFail($id);
        $interaction = FeedbackUserInteraction::firstOrNew(
            ['user_id' => $userId, 'feedback_id' => $id]
        );
        if ($interaction->reported) {
            return response()->json(['message' => 'You have already reported this feedback.'], 400);
        }
        $validator = Validator::make($request->all(), [
            'issue_type'    => 'required|string',
            'issue_details' => 'required|string|min:10',
        ]);
        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }
        $interaction->reported = true;
        $interaction->save();
        $feedback->increment('reports');
        $feedback->report_issue_type = $request->input('issue_type');
        $feedback->report_issue_details = $request->input('issue_details');
        $feedback->save();
        return response()->json([
            'message' => 'Feedback reported successfully!',
            'reports' => $feedback->reports,
        ]);
    }
}
