<?php

namespace App\Http\Controllers\Live;

use App\Http\Controllers\Controller;
use App\Http\Resources\Transaction\CustomerInfoResource;
use App\Models\User;
use Illuminate\Http\Request;

class LiveController extends Controller {
    
    public function getParticipants() { 

        $user = User::query()
            ->has('student')
            ->get();

        if (!$user) {
            return response()->json([
                'data' => 'No users found',
            ]);
        }

        return response()->json([
            'data' => CustomerInfoResource::collection($user),
        ]);
    }
}
