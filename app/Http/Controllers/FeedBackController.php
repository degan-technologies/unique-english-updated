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
use Illuminate\Support\Facades\DB;
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

        $feedbacks = FeedBack::query()
            ->where('instractor_id', $user->id)
            ->with('user')
            ->withCount([
                'feedbackUserInteractions as liked_count' => fn($query) => $query->where('favorite', 'liked'),
                'feedbackUserInteractions as disliked_count' => fn($query) => $query->where('favorite', 'disliked'),
            ])
            ->get();

        $ratingStats = FeedBack::query()
            ->where('instractor_id', $user->id)
            ->selectRaw(
                'AVG(rate) as average_rating,
                SUM(CASE WHEN ROUND(rate) = 5 THEN 1 ELSE 0 END) as star_5,
                SUM(CASE WHEN ROUND(rate) = 4 THEN 1 ELSE 0 END) as star_4,
                SUM(CASE WHEN ROUND(rate) = 3 THEN 1 ELSE 0 END) as star_3,
                SUM(CASE WHEN ROUND(rate) = 2 THEN 1 ELSE 0 END) as star_2,
                SUM(CASE WHEN ROUND(rate) = 1 THEN 1 ELSE 0 END) as star_1'
            )
            ->first();

        $review = [
            'averageRating' => round($ratingStats->average_rating ?? 0, 1),
            'starDistribution' => [
                (int) ($ratingStats->star_5 ?? 0),
                (int) ($ratingStats->star_4 ?? 0),
                (int) ($ratingStats->star_3 ?? 0),
                (int) ($ratingStats->star_2 ?? 0),
                (int) ($ratingStats->star_1 ?? 0),
            ],
        ];

        return response()->json([
            'data'             => FeedBackResource::collection($feedbacks),
            'averageRating'    => $review['averageRating'],
            'starDistribution' => $review['starDistribution'],
        ]);
    }

    public function getFeedbacksByCourse(Request $request, $slug) {
        $modelClass = null;
        $foreignID = null;

        if ($request->feedbackType === 'course') {
            $modelClass = Course::class;
            $foreignID = 'course_id';
        } elseif ($request->feedbackType === 'book') {
            $modelClass = Book::class;
            $foreignID = 'book_id';
        } else {
            return response()->json([
                'message' => $this->langService->getLang('not_found')
            ], 400);
        }


        $currentModelClass = $modelClass::query()
            ->where('slug', $slug)
            ->first();

        if (!$currentModelClass) {
            return response()->json([
                'message' => $this->langService->getLang('not_found')
            ], 404);
        }

        $feedbacks = FeedBack::query()
            ->where($foreignID, $currentModelClass->id)
            ->with('user')
            ->withCount([
                'feedbackUserInteractions as liked_count' => fn($query) => $query->where('favorite', 'liked'),
                'feedbackUserInteractions as disliked_count' => fn($query) => $query->where('favorite', 'disliked'),
            ])
            ->paginate(2);

        if ($feedbacks->isEmpty()) {
            return response()->json([
                'message' => $this->langService->getLang('no_feedbacks_found')
            ], 404);
        }

        $pagination = $feedbacks->toArray();
        unset($pagination['data']);

        $ratingStats = FeedBack::query()
            ->where($foreignID, $currentModelClass->id)
            ->selectRaw(
                'AVG(rate) as average_rating,
                SUM(CASE WHEN ROUND(rate) = 5 THEN 1 ELSE 0 END) as star_5,
                SUM(CASE WHEN ROUND(rate) = 4 THEN 1 ELSE 0 END) as star_4,
                SUM(CASE WHEN ROUND(rate) = 3 THEN 1 ELSE 0 END) as star_3,
                SUM(CASE WHEN ROUND(rate) = 2 THEN 1 ELSE 0 END) as star_2,
                SUM(CASE WHEN ROUND(rate) = 1 THEN 1 ELSE 0 END) as star_1'
            )
            ->first();

        $review = [
            'averageRating' => round($ratingStats->average_rating ?? 0, 1),
            'starDistribution' => [
                (int) ($ratingStats->star_5 ?? 0),
                (int) ($ratingStats->star_4 ?? 0),
                (int) ($ratingStats->star_3 ?? 0),
                (int) ($ratingStats->star_2 ?? 0),
                (int) ($ratingStats->star_1 ?? 0),
            ],
        ];

        return response()->json([
            'pagination'       => $pagination,
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
        $getId = null;
        $columenNmae = null;

        /* @var \App\Models\User $user
        */
        $user = User::query()
            ->where('id', Auth::id())
            ->first();

        if(!$user) {
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

        $feedbackCompleted = $user->feedBacks;

        switch ($feedbackType) {
            case 'course':
                $currentFeedback = Course::query()
                    ->where('slug', $courseSlug)
                    ->first();
                $getId = $currentFeedback->id; 
                $eligibleCourse = Course::checkEligibility($currentFeedback->id);
                $columenNmae = 'course_id';
                break;
            case 'book':
                $currentFeedback = Book::query()
                    ->where('slug', $courseSlug)
                    ->first();
                $getId = $currentFeedback->id;
                $eligibleCourse = Book::checkEligibility($currentFeedback->id);
                $columenNmae = 'book_id';
                break;
            default:
                return response()->json([
                    'message' => $this->langService->getLang('invalid_feedback_type')
                ], 400);
        }

         if(!$currentFeedback) {
            return response()->json([
                'message' => $this->langService->getLang('not_found')
            ], 404);
        }
 
        $feedbackCompleted = FeedBack::query()
            ->where('user_id', $user->id)
            ->where("$columenNmae", $getId)
            ->first(); 

        if($feedbackCompleted){
            return response()->json([
                'message' => $this->langService->getLang('feedback_already_submitted')
            ], 403);
        }
 
        if (!$eligibleCourse && !$user->systemAdmin) {
            return response()->json([
                'message' => $this->langService->getLang('unauthorized_action')
            ], 403);
        } 

        $feedback = $user->feedBacks()->create([
            'rate' => $request->rate,
            'comment' => $request->comment,
            $columenNmae => $getId,
            'instractor_id' => $currentFeedback->user_id,
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
    public function destroy($id) {

        $user = Auth::user();

        if(!$user){
            return response()->json([
                'message' => $this->langService->getLang('unauthorized_action')
            ], 403);
        }

        $feedback = FeedBack::query()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('instractor_id', $user->id)
                    ->orWhereHas('course', function ($q) use ($user) {
                        $q->where('user_id', $user->id); 
                    });
            })
            ->where('id', $id)
            ->first();


        if (!$feedback) {
            return response()->json([
                'message' => $this->langService->getLang('feedback_not_found')
            ], 404);
        }

        try {
            DB::beginTransaction();

            $feedback->deleted_at = now();
            $interactions = $feedback->feedbackUserInteractions;

            if ($interactions->isNotEmpty()) {
                foreach ($interactions as $interaction) {
                    $interaction->delete();
                }
            }
            $feedback->save();

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'message' => $this->langService->getLang('error_deleting_feedback'),
                'error'   => $e->getMessage(),
            ], 500);
        }
        
        return response()->json(['message' => 'Feedback deleted successfully!']);
    }
    /**
     * Like the specified feedback.
     */
    public function addFavorite(Request $request, $id) {
        /**
         * @var User $user
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
