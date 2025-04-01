<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\CurrentUserResource;
use App\Services\LangService;
use Illuminate\Http\Request;

use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller {

    protected $langService;
    use LogsActivity;
    public function __construct(LangService $langService) {
        $this->langService = $langService;
    }

    /**
     * Log in a user using the web guard and issue a Passport token.
     * The token is then stored in an HTTP-only cookie.
     */
    public function login(Request $request) {
        /**
         * @var user $user
         */
        $validation = [
            'email'    => ['required', 'email'],
            'password' => ['required']
        ];

        $validationMessage = [
            'email.required' => $this->langService->getLang('email_required'),
            'email.email'    => $this->langService->getLang('invalid_email'),
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
            'email'    => $request->email,
            'password' => $request->password
        ];

        if (!Auth::guard('web')->attempt($credentials)) {
            return response()->json([
                'message' => $this->langService->getLang('invalid_credentials')
            ], 422);
        }

        $user = Auth::user();
        $token = $user->createToken('AuthToken')->accessToken;

        $cookie = Cookie::make('authToken', $token, 60 * 24 * 7, '/', null, true, false);

        return response()->json([
            'message' => 'Login successful',
            'token' => $token
        ])->withCookie($cookie);
    }

    /**
     * Return the current user information.
     * This method uses the API guard. If the Authorization header
     * is not present, it sets the header from the HTTP-only cookie.
     */
    public function currentUser(Request $request) {
        // If no Authorization header, set it from the authToken cookie
        if (!$request->hasHeader('Authorization')) {
            $token = Cookie::get('authToken');
            if ($token) {
                $request->headers->set('Authorization', 'Bearer ' . $token);
            } else {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
        }

        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json(new CurrentUserResource($user));
    }

    /**
     * Log out the user by revoking the Passport token and deleting the auth cookie.
     */
    public function logout(Request $request) {
        $request->user()->token()->revoke();
        $cookie = Cookie::forget('authToken');

        return response()->json([
            'message' => $this->langService->getLang('logged_out')
        ])->withCookie($cookie);
    }

}
