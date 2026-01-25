<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Resources\System\HeroResource;
use App\Models\System\Hero;
use App\Models\User;
use App\Services\LangService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class HeroController extends Controller
{
    /**
     * get error traslation and success beased on the language 
     * localized 
     */
    protected $langService;

    public function __construct(LangService $langService)
    {
        $this->langService = $langService;
    }

    public function index()
    {
        $hero = Hero::first();

        if (!$hero) {
            $hero = (object)[
                'title' => '',
                'description' => '',
                'logo' => null,
                'banner' => null,
                'background_image' => null,
            ];
        }

        return response()->json([
            'data' => new HeroResource($hero)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * or update the existing one
     */

    public function stroreOrUpdate(Request $request)
    {
        /**
         * Check if the user is authenticated and has admin privileges
         * You may need to adjust this based on your user roles system
         */
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Authentication required'
            ], 401);
        }

        // Check if user has admin privileges (adjust this condition based on your role system)
        // Option 1: Check for systemAdmin relationship
        $hasSystemAdmin = $user->systemAdmin()->exists();

        // Option 2: Check for admin role (uncomment if you use role-based system)
        // $isAdmin = $user->role === 'admin' || $user->is_admin === true;

        // Option 3: Check user type (uncomment if you use user_type field)
        // $isAdmin = $user->user_type === 'admin' || $user->user_type === 'system_admin';

        if (!$hasSystemAdmin) {
            return response()->json([
                'message' => 'Insufficient permissions. Admin access required.'
            ], 403);
        }

        $validationRules = [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // Get the first hero record or create a new one
        $hero = Hero::first();

        if (!$hero) {
            $hero = new Hero();
            $hero->user_id = $user->id;
        }

        // Handle file uploads
        if ($request->hasFile('logo')) {
            if ($hero->logo && Storage::disk('public')->exists($hero->logo)) {
                Storage::disk('public')->delete($hero->logo);
            }
            $logo = $request->file('logo');
            $logoPath = $logo->store('SystemImages', 'public');
            $hero->logo = $logoPath;
        }

        if ($request->hasFile('banner')) {
            if ($hero->banner && Storage::disk('public')->exists($hero->banner)) {
                Storage::disk('public')->delete($hero->banner);
            }
            $banner = $request->file('banner');
            $bannerPath = $banner->store('SystemImages', 'public');
            $hero->banner = $bannerPath;
        }

        if ($request->hasFile('background_image')) {
            if ($hero->background_image && Storage::disk('public')->exists($hero->background_image)) {
                Storage::disk('public')->delete($hero->background_image);
            }
            $backgroundImage = $request->file('background_image');
            $backgroundPath = $backgroundImage->store('SystemImages', 'public');
            $hero->background_image = $backgroundPath;
        }

        // Update text fields
        if ($request->filled('title')) {
            $hero->title = $request->title;
        }

        if ($request->filled('description')) {
            $hero->description = $request->description;
        }

        $hero->save();

        return response()->json([
            'message' => 'Hero section updated successfully!',
            'data' => new HeroResource($hero)
        ], 200);
    }

    /**
     * Delete a specific image from hero section
     */
    public function deleteImage(Request $request, $imageType)
    {
        /**
         * Check if the user is authenticated and has admin privileges
         */
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Authentication required'
            ], 401);
        }

        // Check if user has admin privileges
        $hasSystemAdmin = $user->systemAdmin()->exists();

        if (!$hasSystemAdmin) {
            return response()->json([
                'message' => 'Insufficient permissions. Admin access required.'
            ], 403);
        }

        // Validate image type
        $allowedTypes = ['logo', 'banner', 'background'];
        if (!in_array($imageType, $allowedTypes)) {
            return response()->json([
                'message' => 'Invalid image type'
            ], 400);
        }

        // Get the first hero record
        $hero = Hero::first();

        if (!$hero) {
            return response()->json([
                'message' => 'Hero section not found'
            ], 404);
        }

        // Map the image type to the correct database column
        $columnMap = [
            'logo' => 'logo',
            'banner' => 'banner',
            'background' => 'background_image'
        ];

        $column = $columnMap[$imageType];
        $imagePath = $hero->{$column};

        if (!$imagePath) {
            return response()->json([
                'message' => ucfirst($imageType === 'background' ? 'background image' : $imageType) . ' not found'
            ], 404);
        }

        // Delete the file from storage
        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }

        // Update the database record
        $hero->{$column} = null;
        $hero->save();

        return response()->json([
            'message' => ucfirst($imageType === 'background' ? 'background image' : $imageType) . ' deleted successfully',
            'data' => new HeroResource($hero)
        ], 200);
    }
}
