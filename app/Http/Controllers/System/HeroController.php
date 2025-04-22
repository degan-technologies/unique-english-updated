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

class HeroController extends Controller {
    /**
     * get error traslation and success beased on the language 
     * localized 
     */
    protected $langService;

    public function __construct(LangService $langService) {
        $this->langService = $langService;
    }

    public function index() { 

        $hero = Hero::first();  

        if (!$hero) {

            $hero = [
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

    public function stroreOrUpdate(Request $request) {
 

        /**
         * * Check if the user is a system admin or instructor
         */

        $user = User::query()
            ->where('id', Auth::id())
            ->has('systemAdmin')
            ->first();

        $validationRules = [
            'title' => 'string|max:255',
            'description' => 'string|max:1000',
            'logo' => 'image|mimes:jpeg,png,jpg,gif|max:2048', 
            'banner' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'background_image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('heroMessages'));

        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->all()[0],
                'errors' => $validator->errors()
            ], 422);
        }

        $hero = $user->hero()->first();

        if(!$hero) {
            $hero = new Hero();
            $hero->user_id = $user->id;
        }


        if ($request->hasFile('logo')) {
            if ($hero->logo) {
                Storage::disk('public')->delete($hero->logo);
            }
            $logo = $request->file('logo');
            $logoPath =$logo->store('SystemImages', 'public');
            $hero->logo = $logoPath;
        }
        if ($request->hasFile('banner')) {
            if ($hero->banner) {
                Storage::disk('public')->delete($hero->banner);
            }
            $banner = $request->file('banner');
            $bannerPath = $banner->store('SystemImages', 'public');
            $hero->banner = $bannerPath;
        } 

        if ($request->hasFile('background_image')) {
            if ($hero->background_image) {
                Storage::disk('public')->delete($hero->background_image);
            }
            $backgroundImage = $request->file('background_image');
            $backgroundPath = $backgroundImage->store('SystemImages', 'public');
            $hero->background_image = $backgroundPath;
        }
 
        $hero->title = $request->title;
        $hero->description = $request->description; 
        $hero->save();

        return response()->json([
            'message' => 'Hero created successfully!', 
            'data' => new HeroResource($hero)
        ], 201);
    }

}
