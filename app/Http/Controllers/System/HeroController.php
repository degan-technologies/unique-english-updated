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
                'title'            => '',
                'description'      => '',
                'logo'             => null,
                'banner'           => null,
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
         */
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Authentication required'
            ], 401);
        }

        $hasSystemAdmin = $user->systemAdmin()->exists();

        if (!$hasSystemAdmin) {
            return response()->json([
                'message' => 'Insufficient permissions. Admin access required.'
            ], 403);
        }

        $validationRules = [
            'title'            => 'nullable|string|max:255',
            'description'      => 'nullable|string|max:1000',
            'logo'             => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'banner'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        // Get the first hero record or create a new one
        $hero = Hero::first();

        if (!$hero) {
            $hero          = new Hero();
            $hero->user_id = $user->id;
        }

        // Handle file uploads — all go to S3
        if ($request->hasFile('logo')) {
            // Delete old logo from S3
            if ($hero->logo) {
                Storage::disk('s3')->delete($hero->logo);
            }
            $hero->logo = Storage::disk('s3')->putFile('system/images', $request->file('logo'));
        }

        if ($request->hasFile('banner')) {
            if ($hero->banner) {
                Storage::disk('s3')->delete($hero->banner);
            }
            $hero->banner = Storage::disk('s3')->putFile('system/images', $request->file('banner'));
        }

        if ($request->hasFile('background_image')) {
            if ($hero->background_image) {
                Storage::disk('s3')->delete($hero->background_image);
            }
            $hero->background_image = Storage::disk('s3')->putFile('system/images', $request->file('background_image'));
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
            'data'    => new HeroResource($hero)
        ], 200);
    }

    /**
     * Delete a specific image from hero section
     */
    public function deleteImage(Request $request, $imageType)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Authentication required'
            ], 401);
        }

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

        $hero = Hero::first();

        if (!$hero) {
            return response()->json([
                'message' => 'Hero section not found'
            ], 404);
        }

        $columnMap = [
            'logo'       => 'logo',
            'banner'     => 'banner',
            'background' => 'background_image'
        ];

        $column    = $columnMap[$imageType];
        $imagePath = $hero->{$column};

        if (!$imagePath) {
            return response()->json([
                'message' => ucfirst($imageType === 'background' ? 'background image' : $imageType) . ' not found'
            ], 404);
        }

        // Delete the file from S3
        Storage::disk('s3')->delete($imagePath);

        // Update the database record
        $hero->{$column} = null;
        $hero->save();

        return response()->json([
            'message' => ucfirst($imageType === 'background' ? 'background image' : $imageType) . ' deleted successfully',
            'data'    => new HeroResource($hero)
        ], 200);
    }
}
