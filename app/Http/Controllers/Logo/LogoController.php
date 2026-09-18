<?php

namespace App\Http\Controllers\Logo;


use App\Models\Logo\Logo;

use App\Http\Resources\Logo\LogoResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;


class LogoController extends Controller
{
    public function store(Request $request)
    {
        /**
         * @var \App\Models\User $user
         */
        $user = User::query()
            ->has('systemAdmin')
            ->findOrFail(Auth::id());

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized. Only system administrators can perform this action.',
            ], 403);
        }

        $validationRules = [
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'name' => 'nullable|string|max:255'
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        // Get the first existing logo (assuming only one logo is stored)
        $logo = Logo::first();

        // Delete old logo from S3 if it exists
        if ($logo && $logo->file_path) {
            Storage::disk('s3')->delete($logo->file_path);
        }

        $file     = $request->file('logo');
        $filePath = Storage::disk('s3')->putFile('logos', $file);

        if ($logo) {
            // Update existing logo
            $logo->file_path = $filePath;
            $logo->name      = $request->input('name');
            $logo->save();
        } else {
            // Store new logo
            $logo = Logo::create([
                'name'      => $request->input('name'),
                'file_path' => $filePath
            ]);
        }

        return response()->json([
            'message' => 'Logo successfully uploaded.',
            'data'    => new LogoResource($logo),
        ]);
    }


    // Other methods (index, show) remain as they were, as only the store method was modified
    public function index()
    {
        return LogoResource::collection(Logo::all());
    }

    public function show(Logo $logo)
    {
        return new LogoResource($logo);
    }
    
}
