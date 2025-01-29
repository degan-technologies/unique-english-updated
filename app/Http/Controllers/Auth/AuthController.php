<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\CurrentUserResource;
use App\Services\LangService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller {

    /**
     * get error traslation and success beased on the language 
     * localized 
     */
    protected $langService;

    public function __construct(LangService $langService) {
        $this->langService = $langService;
    }

    /**
     * Login user using email and password
     * if there is a successfull attempt 
     * it will create passport Bearer token
     * and returns it to the user
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function login(Request $request) {
        $validation = [
            'email' => ['required', 'email'],
            'password' => ['required']
        ];

        $validationMessage = [
            'email.required' => $this->langService->getLang('email_required'),
            'email.email' => $this->langService->getLang('invalid_email'),
            'password.required' => $this->langService->getLang('enter_your_password'),
        ];

        $validator = Validator::make($request->all(), $validation, $validationMessage);
        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message
            ], 422);
        }

        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (!Auth::guard('web')->attempt($credentials)) {
            return response()->json([
                'message' => $this->langService->getLang('invalid_credentials')
            ], 422);
        }

        $token = Auth::user()->createToken($request->email);
        return response()->json([
            'token' => $token->accessToken
        ]);
    }

    
    /**
     * Return the current user information
     * based on the given Resource
     * this route should be guarded using the
     * auth:api guard middleware
     * @return mixed
     */
    public function currentUser() {

        /**
         * @var \App\Models\User $user
         */
        $user = Auth::user();
        return response()->json(new CurrentUserResource($user));
    }

    public function logout(Request $request) {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => $this->langService->getLang('logged_out')
        ]);
    }
}