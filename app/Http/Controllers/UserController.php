<?php

namespace App\Http\Controllers;

use App\Helper\PhoneNumberHelper;
use App\Http\Resources\Auth\CurrentUserResource;
use App\Http\Resources\Transaction\CustomerInfoResource;
use App\Mail\OTPVerificationMail;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\userResource;
use App\Models\Role\Instructor;
use App\Models\Role\Student;
use App\Services\LangService;
use App\Traits\AdminActivityLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Container\Attributes\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cookie;

class UserController extends Controller {

    use AdminActivityLog;

    /**
     * get error traslation and success beased on the language
     * localized
     */

    protected $langService;

    public function __construct(LangService $langService)
    {
        $this->langService = $langService;
    }

    public function index(Request $request) { 
        $query = User::query()
            ->doesntHave('systemAdmin')
            ->whereNull('user_banned_at');
 
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
            });
        } 
        // Join date range filter using the created_at field as the join date
        if ($request->filled('joinDateFrom')) {
            $query->whereDate('created_at', '>=', $request->input('joinDateFrom'));
        }
        if ($request->filled('joinDateTo')) {
            $query->whereDate('created_at', '<=', $request->input('joinDateTo'));
        }
  
        $users = $query->orderBy('created_at','DESC')
            ->paginate($request->rowsPerPageOptions);

        $pagination = $users->toArray();
        unset($pagination['data']);


        return response()->json([
            'data' => CustomerInfoResource::collection($users),
            'pagination' =>$pagination,
        ]);
    }

    /**
     * get user sattistics
     */
    public function getUserStatistics() {
        $user = User::query()
            ->has('systemAdmin')
            ->findOrFail(Auth::id());

        $allUser = User::query()
            ->doesntHave('systemAdmin')
            ->get();

        $userCount = $allUser->count();
        $newRegistrations = $allUser->where('created_at', '>=', Carbon::now()->subMonth())->count();
        $activeUsers = $allUser->where('last_login_at', '>=', Carbon::now()->subMonth())->count();

        return response()->json([
            'totalUsers' => $userCount,
            'activeUsers' => $activeUsers, 
            'newRegistrations' => $newRegistrations,
        ]);
    }


    /**
     * Store a newly created user and send OTP.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function studentRegistration(Request $request) {

        $fullname = [];
        $otp = random_int(100000, 999999);

        $fullname = explode(' ', $request->full_name);
        $firstName = $fullname[0];
        $middleName = isset($fullname[1]) ? $fullname[1] : null;
        $lastName = isset($fullname[2]) ? $fullname[2] : null;

        $validationRules = [
            'email'      => 'required|email',
            'full_name' => ['required', 'not_regex:/[\\\\\/\?\%\*\:\|\"<>]/'],            
            'password'   => 'required|min:4',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('registration'));

        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = new User();
            $user->slug       = Str::uuid();
            $user->email      = $request->email;
            $user->first_name = $firstName;
            $user->middle_name = $middleName;
            $user->last_name  = $lastName; 
            $user->password   = Hash::make($request->password);
            $user->role       = STUDENT;

            $user->otp = $otp;
            $user->otp_expires_at = Carbon::now()->addMinutes(10);
            $user->otp_attempts = 0;

            $user->save();
  
            $user->student()->create();

            $url = url('/verify-otp?email=' . $user->email . '&otp=' . $otp);

            Mail::to($user->email)->send(new OTPVerificationMail($otp, $user->first_name, $url));

            DB::commit();

            return response()->json([
                'message' => 'User registered. OTP sent to your email.',
                'data' => new userResource($user)
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    'message' => 'Registration failed.',
                    'error' => $e->getMessage()
                ],
                500
            );
        }
    }

    public function verifyEmailOTP(Request $request) {
        $validationRules = [
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|digits:6',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('email_otp_verification'));

        if (!$validator->passes()) {
            return response()->json(['message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        if (Carbon::now()->greaterThan($user->otp_expires_at)) {
            return response()->json([
                'message' => 'OTP has expired. Please request a new one.'
            ], 422);
        }

        if ($user->otp !== intval(trim($request->otp))) {
            return response()->json([
                'message' => 'Invalid OTP.'
            ], 422);
        }

        $user->email_verified_at = Carbon::now();
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();
 
        $token = $user->createToken('AuthToken')->accessToken;

        $cookie = Cookie::make('authToken', $token, 60 * 24 * 7, '/', null, true, false);

        return response()->json([
            'message' => 'Login successful',
            'token' => $token
        ])->withCookie($cookie);
    }

    public function resendOTP(Request $request)
    {

        $request->validate(['email' => 'required|email|exists:users,email']);
        $validationRules = [
            'email' => 'required|email|exists:users,email',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('email_otpResend_verification'));

        if (!$validator->passes()) {
            return response()->json(['message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Limit OTP resend (3 times max)
        if ($user->otp_attempts >= 3) {
            return response()->json(['message' => 'OTP resend limit reached. Please try later.'], 429);
        }

        $otp = random_int(100000, 999999);
        $user->otp = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(10);
        $user->otp_attempts += 1;
        $user->save();

        $url = url('/verify-otp?email=' . $user->email . '&otp=' . $otp);

        Mail::to($user->email)->send(new OTPVerificationMail($otp, $user->first_name, $url));

        return response()->json(['message' => 'A new OTP has been sent.'], 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function addInstructor(Request $request)
    {
        // Ensure that the current user is allowed to add an instructor
        $canAddinstructor = User::query()
            ->has('systemAdmin')
            ->findOrFail(Auth::id());

        // Validation rules without a password field
        $validationRules = [
            'email'      => 'required|email|unique:users',
            'first_name' => ['required', 'not_regex:/[\\\\\/\?\%\*\:\|\"<>]/', 'alpha_dash:ascii'],
            'middle_name' => ['not_regex:/[\\\\\/\?\%\*\:\|\"<>]/', 'alpha_dash:ascii'],
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('registration'));

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = new User();
            $user->user_id    = $canAddinstructor->id;
            $user->slug       = Str::uuid();
            $user->email      = $request->email;
            $user->first_name = $request->first_name;
            $user->middle_name = $request->middle_name;
 
            $randomPassword = Str::random(8); 
            $user->password = Hash::make($randomPassword);
 
            $user->temp_password = $randomPassword;
 
            $user->role = INSTRUCTOR;
            $user->save();
            $user->created_at = Carbon::now();

            $instructor = new Instructor();
            $instructor->user_id = $user->id;
            $instructor->save();

            $this->adminActivities('Add newn instractor name: ' . $user->first_name . 'and email ' . $user->email);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $this->langService->getLang('registration_failed'),
            ], 500);
        }
        return response()->json([
            'message' => $this->langService->getLang('user_successfully_registered'),
            'data' => new UserResource($user),
        ]);
    }

    public function addStudent(Request $request)
    {
        $canAddinstructor = User::query()
            ->has('systemAdmin')
            ->findOrFail(Auth::id());


        $validationRules = [
            'email' => 'required|email|unique:users',
            'first_name' => ['required', 'not_regex:/[\\\\\/\?\%\*\:\|\"<>]/', 'alpha_dash:ascii'],
            'middle_name' => ['not_regex:/[\\\\\/\?\%\*\:\|\"<>]/', 'alpha_dash:ascii'],
            'password' => 'required|min:4',
            'phone' => 'required',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('registration'));

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
            $user = new User();
            $user->user_id = $canAddinstructor->id;
            $user->slug = Str::uuid();
            $user->email = $request->email;
            $user->first_name = $request->first_name;
            $user->middle_name = $request->middle_name;
            $user->password = Hash::make($request->password);
            $user->phone = $request->phone;
            $user->role = STUDENT;
            $user->save();
            $user->created_at = Carbon::now();

            $instructor = new Instructor();
            $instructor->user_id = $user->id;
            $instructor->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $this->langService->getLang('registration_failed'),
            ], 500);
        }
        return response()->json([
            'message' => $this->langService->getLang('user_successfully_registered'),
            'data' => new UserResource($user),
        ]);
    }


    /**
     * Delete a user
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $canDeleteUser = User::query()
            ->has('systemAdmin')
            ->findOrFail(Auth::id());

        $user = User::findOrFail($id);
        $user->update([
            'user_banned_at' => Carbon::now(),
        ]);

        $user->delete();

        $this->adminActivities('Delete user name: ' . $user->first_name . 'and email ' . $user->email);

        return response()->json([
            'message' => $this->langService->getLang('user_successfully_deleted')
        ]);
    }

    /**
     * update profile
     */
    public function profileUpdate(Request $request)
    {
        /**
         * @var \App\Models\User $user
         */

         $fullname = [];

            $fullname = explode(' ', $request->full_name);

            $firstName = $fullname[0];
            $middleName = isset($fullname[1]) ? $fullname[1] : null;
            $lastName = isset($fullname[2]) ? $fullname[2] : null;


        $user = Auth::user();

        $validationRules = [
            'email' => 'required|email|unique:users,email,' . $user->id, 
            'phone' => ['unique:users,phone,' . $user->id, 'regex:/^\+[1-9]\d{1,14}$/'], 
            'gender' => [Rule::in(GENDER)],
        ];


        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('registration'));
        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        } 

        $user->update([
            'email' => $request->email ?? $user->email,
            'phone' => $request->phone ?? $user->phone,
            'gender' => $request->gender ?? $user->gender,
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName, 
            'updated_at' => Carbon::now(),
        ]);

        return response()->json([
            'message' => $this->langService->getLang('profile_successfully_updated'),
            'data' => new CurrentUserResource($user),
        ]);
    }


    public function profileImageUpdate(Request $request) {
        
        /**
         * @var \App\Models\User $user
         */ 
        $user = Auth::user();

        $validationRules = [   
            'profile' => 'image',
            'bg_image' => 'image', 
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('registration'));
        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }
 

        if ($request->hasFile('profile')) { 
            $file = $request->file('profile');
            $profilePath = $file->store('/user', 'public');
            $user->profile = $profilePath;
        }

        if ($request->hasFile('bg_image')) {
            $file = $request->file('bg_image');
            $bgPath = $file->store('/user', 'public');
            $user->bg_image = $bgPath;
        } 

        $user->save();

        return response()->json([
            'message' => $this->langService->getLang('profile_successfully_updated'),
            'data' => new CurrentUserResource($user),
        ]);
    }

    public function profileImageRemove(Request $request) {
        
        /**
         * @var \App\Models\User $user
         */ 
        $user = Auth::user();

        $field = $request->input('field') ?? null;

        $validationRules = [
            'field' => 'required|in:profile,bg_image',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('registration'));
        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }


        if ($field && isset($user[$field])) {
            $user[$field] = null;
        }
        $user->save();

        return response()->json([
            'message' => $this->langService->getLang('profile_successfully_updated'),
            'data' => new CurrentUserResource($user),
        ]);
    }

    /**
     * Reset the password of the current user
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function passwordReset(Request $request)
    {

        /**
         * @var \App\Models\User $user
         */
        $user = Auth::user();

        $validation = [
            'old_password' => ['required'],
            'new_password' => ['required', 'confirmed', 'min:8'],
            'new_password_confirmation' => ['required'],
        ];

        $validator = Validator::make($request->all(), $validation, $this->langService->getLang('password_reset'));

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message
            ], 422);
        }

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'message' => $this->langService->getLang('incorrect_old_password')
            ], 422);
        }

        $user->password = bcrypt($request->new_password);
        $user->save();

        $this->adminActivities('password was changed');

        return response()->json([
            'message' => $this->langService->getLang('password_changed')
        ]);
    }

    /**
     * Bulk delete users.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function bulkDelete(Request $request) {
        $validator = Validator::make($request->all(), [
            'ids'   => 'required|array',
            'ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid user IDs provided.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $userIds = $request->ids; 
        User::whereIn('id', $userIds)->update([
            'user_banned_at' => Carbon::now()
        ]);

        $this->adminActivities('Bulk banned ' . count($userIds) . ' users');

        return response()->json([
            'message' => $this->langService->getLang('user_successfully_deleted')
        ]);
    }
}
