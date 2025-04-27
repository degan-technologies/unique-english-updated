<?php

namespace App\Http\Controllers;

use App\Services\JitsiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JitsiController extends Controller
{
    /**
     * Generate JWT token for a Jitsi room.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateToken(Request $request)
    {
        $validated = $request->validate([
            'room' => 'required|string|max:255',       
            'user.email' => 'required|email',          
            'user.name' => 'required|string',          
        ]);

        try {
            $token = app(JitsiService::class)->generateToken(
                $request->room,    
                $request->user      
            );

            // Return the generated token as a JSON response
            return response()->json([
                'token' => $token
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error generating Jitsi token: ' . $e->getMessage());

            // Return a generic error message to the frontend
            return response()->json([
                'error' => 'Failed to generate Jitsi token. Please try again later.'
            ], 500);
        }
    }
}