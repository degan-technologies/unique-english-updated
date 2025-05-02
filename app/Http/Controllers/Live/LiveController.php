<?php

namespace App\Http\Controllers\Live;

use App\Http\Controllers\Controller;
use App\Http\Resources\Live\LiveParticipantsResource;
use App\Http\Resources\Live\RoomResource;
use App\Http\Resources\Transaction\CustomerInfoResource;
use App\Models\Live\GroupRoom;
use App\Models\Live\LiveRooms;
use App\Models\User;
use App\Services\LangService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LiveController extends Controller {

    /**
     * get error traslation and success beased on the language 
     * localized 
     */
    protected $langService;

    public function __construct(LangService $langService) {
        $this->langService = $langService;
    }

    public function getAllRooms() {
        $rooms = LiveRooms::query()
            ->where(function($query) {
                $query->orWhere('user_id', Auth::id())
                    ->orWhere('instructor_id', Auth::id());
            })
            ->orderBy("created_at","asc")
            ->get();

        if (!$rooms) {
            return response()->json([
                'data' => 'No rooms found',
            ]);
        }

        return response()->json([
            'data' => RoomResource::collection($rooms),
        ]);
    }
    
    public function getParticipants() { 

        $user = User::query()
            ->whereHas('customerTransactions', function($query) {
                $query->where('status', TRANSACTION_SUCCESS)
                      ->where('product_type', LIVE_CLASS);
            })
            ->get();

        if (!$user) {
            return response()->json([
                'data' => 'No users found',
            ]);
        }

        return response()->json([
            'data' => LiveParticipantsResource::collection($user),
        ]);
    }

    public function getMyStudents() { 
        $user = User::query()
        ->where('id', Auth::id())
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$user) {
            return response()->json([
                'data' => 'No users found',
            ]);
        }

        $liveRooms = LiveRooms::query()
            ->where('instructor_id', $user->id)
            ->get();
        
        if (!$liveRooms) {
            return response()->json([
                'data' => 'No rooms found',
            ]);
        }

        $user = User::query()
            ->whereHas('customerTransactions', function($query) {
                $query->where('status', TRANSACTION_SUCCESS)
                      ->where('product_type', LIVE_CLASS);
            })
            ->get();

        if (!$user) {
            return response()->json([
                'data' => 'No users found',
            ]);
        }

        return response()->json([
            'data' => LiveParticipantsResource::collection($user),
        ]);
    }

    public function store(Request $request) {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->where('id', Auth::id())
            ->first();
        
        if (!$user) {
            return response()->json([
                'message' => 'You are not authorized to create a room',
            ], 403);
        }

        $validationRules = [
            'class_name' => [ 'required', 'string', 'max:255', 'unique:live_rooms,class_name,NULL,id,user_id,' . $user->id ],
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('live_rooms'));

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }

        $room = LiveRooms::create([
            'class_name' => $request->class_name, 
            'user_id' => $user->id
        ]);

        return response()->json([
            'message' => 'Room created successfully',
            'data' => new RoomResource($room)
        ], 201);
    }

    public function update(Request $request, $id) {

        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->where('id', Auth::id())
            ->first();

        $room = LiveRooms::query()
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$room) {
            return response()->json([
               'message' => 'Room not found',
            ], 404);
        }

        $validationRules = [
            'class_name' => [ 'required', 'string', 'max:255', 'unique:live_rooms,class_name,' . $id . ',id,user_id,' . $user->id ],
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('live_rooms'));

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }

        $room->update([
            'class_name' => $request->class_name, 
        ]);

        return response()->json([
            'message' => 'Room updated successfully',
            'data' => new RoomResource($room)
        ]);
    }

    public function assignClass(Request $request, $id) {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->where('id', Auth::id())
            ->first();

        $liveRoom = LiveRooms::query()
            ->where('user_id', $user->id)
            ->where('id', $request->classId)
            ->first();

        if (!$liveRoom) {
            return response()->json([
              'message' => 'Room not found',
            ], 404);
        }

        $groupRoom = GroupRoom::query()
            ->where('user_id', $id) 
            ->first();
        
        if ($groupRoom) {
            $groupRoom->update([
                'live_room_id' => $request->classId,
            ]);
            return response()->json([
               'message' => 'Room assigned successfully',
                'data' => $groupRoom
            ]);
        }

        $groupRoom = $liveRoom->groupRooms()->create([
            'user_id' => $id,
        ]);

        return response()->json([
           'message' => 'Room assigned successfully',
            'data' => $groupRoom
        ]);
    }

    public function AssignInstructors(Request $request, $id) {
        $user = User::query()
            ->whereSystemAdminOrInstructor() 
            ->first();

        $liveRoom = LiveRooms::query()
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$liveRoom) {
            return response()->json([
             'message' => 'Room not found',
            ], 404);
        }

        $instructor = User::query()
            ->where('id', $request->instructor_id)
            ->where(function($query) {
                $query->orWhereHas('systemAdmin')
                    ->orWhereHas('instructor');
            })
            ->first();

        if (!$instructor) {
            return response()->json([
            'message' => 'Instructor not found',
            ], 404);
        }

        $liveRoom->update([
            'instructor_id' => $instructor->id,
        ]);

        return response()->json([
           'message' => 'Instructor added successfully',
            'data' => new RoomResource($liveRoom)
        ]);
    }

    public function getMyInstructors() {
        $user = User::query()
            ->whereSystemAdminOrInstructor() 
            ->first();

        $instructors = User::query()
            ->where('user_id', $user->id)
            ->where(function($query) {
                $query->orWhereHas('systemAdmin')
                    ->orWhereHas('instructor');
            })
            ->orWhere('id', $user->id)
            ->get();

        if (!$instructors) {
            return response()->json([
             'message' => 'No instructors found',
            ], 404);
        }

        return response()->json([
            'message' => 'Instructors found',
            'data' => CustomerInfoResource::collection($instructors)
        ]);
    }
}
