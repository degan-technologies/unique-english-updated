<?php

namespace App\Http\Controllers\Live;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\LangService;
use App\Models\Live\LiveSession;
use App\Models\Live\Participant;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Live\ParticipantResource;

class ParticipantController extends Controller
{
    protected $langService;

    public function __construct(LangService $langService)
    {
        $this->langService = $langService;
    }

    /**
     * Display a listing of the participants.
     */
    public function index() {
     $participants = Participant::query()
        ->where('user_id', Auth::id())
        ->paginate(10);

        $pagination = $participants->toArray();
        unset($pagination['data']);

        return response()->json([
            'pagination' => $pagination,
            'data' => ParticipantResource::collection($participants),
        ]);
    }

    /**
     * Store a newly created participant in storage.
     */
    public function store(Request $request) {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();
            if (!$user) return;
          
            $liveSessionId = $request->live_session_id ?? null;
  
            $liveSession= LiveSession::query()
                         ->where('user_id', $user->id)
                         ->find($liveSessionId );
             
             if(!$liveSession) {
                 return response()->json([
                     'message' => $this->langService->getLang('live_session_not_found')
                 ], 404);
             }

        $validationRules = [
            'joined_at' => 'required|date',
            'status' => ['required', Rule::in(PARTICIPANT_STATUS)],

        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('Participant'));

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }

        $status = $request->status ?? 'registered';

        $participant = $user->participants()->create([
            'slug' => Str::uuid(),
            'joined_at' => $request->joined_at,
            'status' => $status,
            'live_session_id' => $liveSession->id,  
        ]);

        return response()->json([
            'message' => $this->langService->getLang('participant_created_successfully'),
            'data' => new ParticipantResource($participant),
        ]);
    }

    /**
     * Display the specified participant.
     */
    public function show($id)
    {
        $participant = Participant::query()
            ->where('user_id', Auth::id())
            ->first();
            if (!$user) return;

        $liveSession = $participant->liveSession;

        return response()->json([
            'data' => new ParticipantResource($participant),
            'live_session' => $liveSession,  
        ]);
    }

    /**
     * Update the specified participant in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->firstOrFail();
            if (!$user) return;

     
        $liveSessionId = $request->live_session_id ?? null;
  
        $liveSession= LiveSession::query()
                     ->where('user_id', $user->id)
                     ->find($liveSessionId );
         
         if(!$liveSession) {
             return response()->json([
                 'message' => $this->langService->getLang('live_session_not_found')
             ], 404);
         }

         $participant = Participant::query()
         ->where('user_id', $user->id)
         ->findOrFail($id);

     if (!$participant) {
         return response()->json([
             'message' => $this->langService->getLang('participant_not_found'),
         ], 404);
     }
     
        $validationRules = [
            'joined_at' => 'required|date',
            'status' => ['required', Rule::in(PARTICIPANT_STATUS)],
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('Participant'));

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors' => $validator->errors(),
            ], 422);
        }

        $validatedData = $validator->validated();
        $participant->update($validatedData);

        return response()->json([
            'message' => $this->langService->getLang('participant_updated_successfully'),
            'data' => new ParticipantResource($participant),
        ]);
    }

    /**
     * Remove the specified participant from storage.
     */
    public function destroy($id)
    {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->firstOrFail();

        $participant = Participant::query()
            ->where('user_id', $user->id)
            ->findOrFail($id);

        if (!$participant) {
            return response()->json([
                'message' => $this->langService->getLang('participant_not_found'),
            ], 404);
        }

        $participant->delete();

        return response()->json([
            'message' => $this->langService->getLang('participant_deleted_successfully'),
        ]);
    }
}
