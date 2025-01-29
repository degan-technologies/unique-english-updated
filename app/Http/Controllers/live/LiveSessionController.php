<?php

namespace App\Http\Controllers\Live;

use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Live\LiveSession;
use Illuminate\Http\Request;
use App\Services\LangService;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Live\LiveSessionResource;

class LiveSessionController extends Controller
{
    protected $langService;

    public function __construct(LangService $langService)
    {
        $this->langService = $langService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sessions = LiveSession::all()
        ->where('user_id', Auth::id())
        ->paginate(10);

        $pagination = $sessions->toArray();
        unset($pagination['data']);

        return response()->json([
            'pagination' => $pagination,
            'data' => LiveSessionResource::collection($sessions),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = User::query()
        ->whereSystemAdminOrInstructor()
        ->first();
        if (!$user) return;
        
        $validationRules = [
            'title' => 'required|string|max:255',
            'start_time' => 'required|date|after:now',
            'description' => 'required|string|max:1000',
            'end_time' => 'required|date|after:start_time',
            'max_participants' => 'required|integer|min:1|max:500',
            'status' => ['required', Rule::in(LiVESESSION_STATUS)],
            'stream_url' => 'required|url|unique:live_sessions,stream_url',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('LiveSession'));

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $status = $request->status ?? 'completed';

        $session=$user->liveSessions()->create([
            'slug' => Str::uuid(),
            'status'=>$status,
            'title'=>$request->title,
            'instructor_id' => $user->id,
            'end_time' =>$request->end_time,
            'stream_url' =>$request->stream_url,
            'description' =>$request->description,
            'max_participants' =>$request->max_participants,
        ]);

        return response()->json([
            'message' => $this->langService->getLang('session_created_successfully'),
            'data' => new LiveSessionResource($session),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $session = LiveSession::query()
        ->where('user_id', Auth::id())
        ->first();

        return response()->json([
            'data' => new LiveSessionResource($session),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        $user = User::query()
        ->whereSystemAdminOrInstructor()
        ->first();

        $session = LiveSession::query()
        ->where('user_id', Auth::id())
        ->findOrFail($id);

        if(!$session) {
            return response()->json([
                'message' => $this->langService->getLang('session_not_found'),
            ], 404);
        }

        $validationRules = [
            'title' => 'string|max:255',
            'start_time' => 'date|after:now',
            'description' => 'string|max:1000',
            'instructor_id' => 'exists:users,id',
            'end_time' => 'date|after:start_time',
            'max_participants' => 'integer|min:1|max:500',
            'status' => ['required', Rule::in(LiVESESSION_STATUS)],
            'stream_url' => 'url|unique:live_sessions,stream_url,' . $session->id,
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('LiveSession'));

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $validatedData = $validator->validated();
        $session->update($validatedData);

        return response()->json([
            'message' => $this->langService->getLang('session_updated_successfully'),
            'data' => new LiveSessionResource($session),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::query()
        ->whereSystemAdminOrInstructor()
        ->first();

        $session = LiveSession::query()
        ->where('user_id', $user->id)
        ->findOrFail($id);

        if (!$session) {
            return response()->json([
                'message' => $this->langService->getLang('session_not_found'),
            ], 404);
        }

        $session->delete();

        return response()->json([
            'message' => $this->langService->getLang('session_deleted_successfully'),
        ]);
    }
}
