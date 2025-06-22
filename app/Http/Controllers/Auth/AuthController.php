<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\CurrentUserResource;
use App\Mail\OTPVerificationMail;
use App\Models\User;
use App\Services\LangService;
use App\Traits\AdminActivityLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller {

    protected $langService;
    use AdminActivityLog;
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

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => $this->langService->getLang('invalid_credentials'),
            ], 404);
        }

        if ($user->user_banned_at) {
            return response()->json([
                'message' => 'User is banned, please contact support.'
            ], 403);
        }


        if (!Auth::guard('web')->attempt($credentials)) {
            return response()->json([
                'message' => $this->langService->getLang('invalid_credentials')
            ], 422);
        }

        $user = Auth::user();
        $token = $user->createToken('AuthToken')->accessToken;

        $cookie = Cookie::make('authToken', $token, 60 * 24 * 7, '/', null, true, false);

        $this->adminActivities('login');

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

        $this->adminActivities('logout');

        return response()->json([
            'message' => $this->langService->getLang('logged_out')
        ])->withCookie($cookie);
    }

    public function sendResetOtp(Request $request) {
        $otp = random_int(100000, 999999);

        $validationRules = [
            'email' => 'required|email|exists:users,email'
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('registration'));

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::query()
            ->where('email', $request->email)
            ->first();

        $user->otp = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(10);
        $user->otp_attempts = 0;

        $user->save();

        if(!$user) {
            return response()->json([
               'message' => $this->langService->getLang('user_not_found')
            ], 404);
        } 

        $url = url();

        Mail::to($user->email)->send(new OTPVerificationMail($otp, $user->first_name, $url));

        return response()->json([
           'message' => $this->langService->getLang('otp_sent')
        ]);
    }

    public function resetPasswordViaOtp(Request $request){ 
        $validationRules = [
            'email' =>'required|email|exists:users,email',
            'otp' =>'required|integer',
            'password' =>'required|min:8',
            'password_confirmation' =>'required|same:password'
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('registration'));

        if($validator->fails()) {
            $message = $validator->errors()->all()[0];
            return response()->json([
               'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::query()
            ->where('email', $request->email) 
            ->first();

        if(!$user) {
            return response()->json([
              'message' => $this->langService->getLang('user_not_found')
            ], 404);
        }

        if($user->otp != $request->otp) {
            return response()->json([
            'message' => $this->langService->getLang('invalid_otp')
            ]);
        }

        if($user->otp_expires_at < Carbon::now()) {
            return response()->json([
             'message' => $this->langService->getLang('otp_expired')
            ], 404);
        }

        $user->password = bcrypt($request->password);
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->otp_attempts = 0;
        $user->save();

        return response()->json([
          'message' => $this->langService->getLang('password_changed')
        ]);
    }

}
