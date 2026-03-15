<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\CurrentUserResource;
use App\Mail\OTPVerificationMail;
use App\Mail\PasswordResetOTPMail;
use App\Mail\LoginEmailVerificationMail;
use App\Models\User;
use App\Services\LangService;
use App\Traits\AdminActivityLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    protected $langService;
    use AdminActivityLog;
    public function __construct(LangService $langService)
    {
        $this->langService = $langService;
    }

    /**
     * Log in a user using the web guard and issue a Passport token.
     * The token is then stored in an HTTP-only cookie.
     */
    public function login(Request $request)
    {
        $validation = [
            'phone'    => ['nullable', 'string'],
            'email'    => ['nullable', 'email'],
            'password' => ['required']
        ];
        $validationMessage = [
            'phone.required_without' => $this->langService->getLang('phone_or_email_required'),
            'email.required_without' => $this->langService->getLang('phone_or_email_required'),
            'email.email'    => $this->langService->getLang('invalid_email'),
            'password.required' => $this->langService->getLang('enter_your_password'),
        ];

        $validator = Validator::make($request->all(), $validation, $validationMessage);
        if (!$validator->passes() || (!$request->phone && !$request->email)) {
            $message = $validator->errors()->all()[0] ?? $this->langService->getLang('phone_or_email_required');
            return response()->json([
                'message' => $message
            ], 422);
        }

        $user = null;
        if ($request->filled('phone')) {
            $user = User::where('phone', $request->phone)->first();
        } elseif ($request->filled('email')) {
            $user = User::where('email', $request->email)->first();
        }

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

        $credentials = [
            $request->filled('phone') ? 'phone' : 'email' => $request->filled('phone') ? $request->phone : $request->email,
            'password' => $request->password
        ];

        if (!Auth::guard('web')->attempt($credentials)) {
            return response()->json([
                'message' => $this->langService->getLang('invalid_credentials')
            ], 422);
        }

        /**
         * @var User $user
         */
        $user = Auth::user();

        // Check if email login and email is not verified
        if ($request->filled('email') && !$user->email_verified_at) {
            // Generate new OTP for email verification
            $otp = random_int(100000, 999999);
            $user->otp = $otp;
            $user->otp_expires_at = Carbon::now()->addMinutes(10);
            $user->otp_attempts = 0;
            $user->save();

            // Send login email verification OTP
            $supportUrl = url('/support');
            Mail::to($user->email)->send(new LoginEmailVerificationMail($otp, $user->first_name, $user->email, $supportUrl));

            // Logout the user since email is not verified
            Auth::logout();

            return response()->json([
                'message' => 'Please verify your email address first. We have sent you a verification code.',
                'requires_verification' => true,
                'email' => $user->email
            ], 200);
        }

        // Complete login for phone users or verified email users
        $user->save();

        $rememberMe = (bool) $request->input('remember_me', false);
        $minutesUntilExpiry = $rememberMe ? (60 * 24 * 30) : 60; // 30 days or 60 minutes

        // Capture the full token result so we can expose the expiry time.
        // The raw token string still goes only into the HttpOnly cookie — never
        // the response body. But we CAN safely tell the frontend *when* the
        // Passport token expires so it can schedule a proactive logout timer.
        $tokenResult  = $user->createToken('AuthToken');
        $token        = $tokenResult->accessToken;
        $expiresAt    = $tokenResult->token->expires_at->toIso8601String();

        // Store token ONLY in an HttpOnly cookie — never expose it to JavaScript.
        // httpOnly: true  → JS cannot read this cookie (XSS-safe)
        // secure: true    → only sent over HTTPS
        // sameSite: Strict → sent only on same-site requests (CSRF-safe)
        // secure: only force HTTPS in production — allows HTTP on localhost/dev.
        // httpOnly: true always — JS must never read the token.
        // sameSite: 'Strict' for CSRF protection; relax to 'Lax' if needed for OAuth flows.
        $isSecure = app()->environment('production');

        $cookie = Cookie::make(
            'authToken',
            $token,
            $minutesUntilExpiry,
            '/',
            null,
            $isSecure, // secure: true in production, false in local/dev
            true,      // httpOnly: always true — JS cannot read the token
            false,     // raw
            'Strict'   // sameSite
        );

        $this->adminActivities('login');

        // Return the expires_at so the frontend can schedule a proactive logout
        // timer. The actual token is NOT in the body — only in the HttpOnly cookie.
        return response()->json([
            'message'    => 'Login successful',
            'expires_at' => $expiresAt,
        ])->withCookie($cookie);
    }

    /**
     * Return the current user information.
     * This method uses the API guard. If the Authorization header
     * is not present, it sets the header from the HTTP-only cookie.
     */
    public function currentUser(Request $request)
    {
        // InjectBearerTokenFromCookie middleware already handled the cookie → header
        // injection before this controller runs, so we just check auth normally.
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json(new CurrentUserResource($user));
    }

    /**
     * Log out the user by revoking the Passport token and deleting the auth cookie.
     */
    public function logout(Request $request)
    {
        // Revoke the Passport access token server-side
        $request->user('api')?->token()?->revoke();

        // Expire the HttpOnly cookie immediately
        $cookie = Cookie::forget('authToken');

        $this->adminActivities('logout');

        return response()->json([
            'message' => $this->langService->getLang('logged_out')
        ])->withCookie($cookie);
    }

    public function sendResetOtp(Request $request)
    {
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

        if (!$user) {
            return response()->json([
                'message' => $this->langService->getLang('user_not_found')
            ], 404);
        }

        $user->otp = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(10);
        $user->otp_attempts = 0;

        $user->save();

        $supportUrl = url('/support');

        try {
            Mail::to($user->email)->send(new PasswordResetOTPMail($otp, $user->first_name, $supportUrl));
        } catch (\Throwable $e) {
            Log::error('Failed to send password reset OTP email.', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to send reset email right now. Please try again later.'
            ], 500);
        }

        return response()->json([
            'message' => $this->langService->getLang('otp_sent')
        ]);
    }

    public function resetPasswordViaOtp(Request $request)
    {
        $validationRules = [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|integer',
            'password' => ['required', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).+$/'],
            'password_confirmation' => 'required|same:password'
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('registration'));

        if ($validator->fails()) {
            $message = $validator->errors()->all()[0];
            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::query()
            ->where('email', $request->email)
            ->first();

        if (!$user) {
            return response()->json([
                'message' => $this->langService->getLang('user_not_found')
            ], 404);
        }

        if ($user->otp != $request->otp) {
            return response()->json([
                'message' => $this->langService->getLang('invalid_otp')
            ]);
        }

        if ($user->otp_expires_at < Carbon::now()) {
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
