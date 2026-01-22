<?php

namespace App\Http\Controllers;

use App\Http\Resources\Auth\CurrentUserResource;
use App\Http\Resources\Transaction\CustomerInfoResource;
use App\Mail\OTPVerificationMail;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\userResource;
use App\Models\Role\Instructor;
use App\Services\LangService;
use App\Traits\AdminActivityLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cookie;

class UserController extends Controller
{
    use AdminActivityLog;

    protected $langService;

    public function __construct(LangService $langService)
    {
        $this->langService = $langService;
    }

    public function index(Request $request)
    {
        $query = User::query()
            ->doesntHave('systemAdmin');

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

        $users = $query->orderBy('created_at', 'DESC')
            ->paginate($request->rowsPerPageOptions);

        $pagination = $users->toArray();
        unset($pagination['data']);


        return response()->json([
            'data' => CustomerInfoResource::collection($users),
            'pagination' => $pagination,
        ]);
    }

    /**
     * get user sattistics
     */
    public function getUserStatistics()
    {
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
    public function studentRegistration(Request $request)
    {
        $fullname = [];
        $fullname = explode(' ', $request->full_name);
        $firstName = $fullname[0];
        $middleName = isset($fullname[1]) ? $fullname[1] : null;
        $lastName = isset($fullname[2]) ? $fullname[2] : null;

        $validationRules = [
            'phone'      => 'nullable|unique:users,phone|regex:/^\+?[1-9]\d{1,14}$/|required_without:email',
            'email'      => 'nullable|email|unique:users,email|required_without:phone',
            'full_name'  => ['required', 'not_regex:/[\\\\\/\?\%\*\:\|\"<>]/'],
            'password'   => 'required|min:4',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('registration'));

        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if this is email registration (user provided email in original request) or phone registration
        $isEmailRegistration = $request->has('email') && !empty($request->input('email'));

        try {
            DB::beginTransaction();

            $user = new User();
            $user->slug        = Str::uuid();
            $user->phone       = $request->phone;
            $user->email       = $request->email; // Keep original email or null if not provided
            $user->first_name  = $firstName;
            $user->middle_name = $middleName;
            $user->last_name   = $lastName;
            $user->password    = Hash::make($request->password);
            $user->role        = STUDENT;

            if ($isEmailRegistration) {
                // Email registration - send OTP
                $otp = random_int(100000, 999999);
                $user->otp = $otp;
                $user->otp_expires_at = Carbon::now()->addMinutes(10);
                $user->otp_attempts = 0;
            } else {
                // Phone registration - verify directly
                $user->email_verified_at = Carbon::now();
            }

            $user->save();
            $user->student()->create();

            // Send OTP email only for email registration
            if ($isEmailRegistration) {
                $verificationUrl = (string) url('/verify-email?email=' . urlencode($user->email));
                Mail::to($user->email)->send(new OTPVerificationMail($user->otp, $user->first_name, $verificationUrl));

                DB::commit();
                return response()->json([
                    'message' => 'Registration successful. Please check your email for OTP verification.',
                    'requires_otp' => true,
                ], 201);
            }

            // For phone registration, login user directly
            DB::commit();
            Auth::loginUsingId($user->id);
            $token = $user->createToken('AuthToken')->accessToken;
            $cookie = Cookie::make('authToken', $token, 60 * 24 * 7, '/', null, true, false);

            return response()->json([
                'message' => 'User registered successfully.',
                'token' => $token,
                'user' => new CurrentUserResource($user)
            ])->withCookie($cookie);
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

    public function verifyEmailOTP(Request $request)
    {
        $validationRules = [
            'contact_info' => 'required',
            'otp'   => 'required|digits:6',
            'registration_method' => 'required|in:email,phone',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('email_otp_verification'));

        if (!$validator->passes()) {
            return response()->json(['message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        // Find user by email or phone based on registration method
        if ($request->registration_method === 'email') {
            $user = User::where('email', $request->contact_info)->first();
        } else {
            $user = User::where('phone', $request->contact_info)->first();
        }

        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        if (!$user->otp_expires_at || Carbon::now()->greaterThan($user->otp_expires_at)) {
            return response()->json([
                'message' => 'OTP has expired. Please request a new one.'
            ], 422);
        }

        // Convert both OTP values to string for comparison
        if (strval($user->otp) !== strval($request->otp)) {
            return response()->json([
                'message' => 'Invalid OTP.'
            ], 422);
        }

        // Mark as verified based on registration method
        if ($request->registration_method === 'email') {
            $user->email_verified_at = Carbon::now();
        } else {
            $user->phone_verified_at = Carbon::now(); // You might need to add this column to users table
        }

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
        $validationRules = [
            'contact_info' => 'required',
            'registration_method' => 'required|in:email,phone',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('email_otpResend_verification'));

        if (!$validator->passes()) {
            return response()->json(['message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        // Find user by email or phone based on registration method
        if ($request->registration_method === 'email') {
            $user = User::where('email', $request->contact_info)->first();
        } else {
            $user = User::where('phone', $request->contact_info)->first();
        }

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

        // Send OTP based on registration method
        if ($request->registration_method === 'email') {
            $verificationUrl = (string) url('/verify-email?email=' . urlencode($user->email));
            Mail::to($user->email)->send(new OTPVerificationMail($otp, $user->first_name, $verificationUrl));
        } else {
            // SMS OTP sending would go here
        }

        return response()->json(['message' => 'A new OTP has been sent to your ' . $request->registration_method . '.'], 200);
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
        $canAddStudent = User::query()
            ->has('systemAdmin')
            ->findOrFail(Auth::id());

        $validationRules = [
            'email' => 'required|email|unique:users',
            'first_name' => ['required', 'not_regex:/[\\\\\/\?\%\*\:\|\"<>]/', 'alpha_dash:ascii'],
            'middle_name' => ['nullable', 'not_regex:/[\\\\\/\?\%\*\:\|\"<>]/', 'alpha_dash:ascii'],
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
            $user->user_id = $canAddStudent->id;
            $user->slug = Str::uuid();
            $user->email = $request->email;
            $user->first_name = $request->first_name;
            $user->middle_name = $request->middle_name;
            $user->password = Hash::make($request->password);
            $user->phone = $request->phone;
            $user->role = STUDENT;
            $user->save();

            // Create student relationship
            $user->student()->create();

            $this->adminActivities('Added new student name: ' . $user->first_name . ' and email ' . $user->email);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $this->langService->getLang('registration_failed'),
                'error' => $e->getMessage()
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

        if ($user->user_banned_at === null) {
            $user->user_banned_at = now();
            $action = 'banned';
        } else {
            $user->user_banned_at = null;
            $action = 'unbanned';
        }
        $user->save();

        $this->adminActivities("User {$action}: " . $user->first_name . ' and email ' . $user->email);

        return response()->json([
            'message' => "User successfully {$action}"
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
            'phone' => ['unique:users,phone,' . $user->id],
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


        $user->email = $request->email ?? $user->email;
        $user->phone = $request->phone ?? $user->phone;
        $user->gender = $request->gender ?? $user->gender;
        $user->first_name = $firstName;
        $user->middle_name = $middleName;
        $user->last_name = $lastName;
        $user->updated_at = Carbon::now();
        $user->save();

        return response()->json([
            'message' => $this->langService->getLang('profile_successfully_updated'),
            'data' => new CurrentUserResource($user),
        ]);
    }


    public function profileImageUpdate(Request $request)
    {

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

    public function profileImageRemove(Request $request)
    {

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
    public function bulkDelete(Request $request)
    {
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
            'user_banned_at' => DB::raw('CASE 
                WHEN user_banned_at IS NULL THEN NOW() 
                ELSE NULL 
            END')
        ]);

        $this->adminActivities('Bulk banned ' . count($userIds) . ' users');

        return response()->json([
            'message' => $this->langService->getLang('user_successfully_deleted')
        ]);
    }

    /**
     * Get instructors only
     */
    public function getInstructors(Request $request)
    {
        $query = User::query()
            ->doesntHave('systemAdmin')
            ->where('role', INSTRUCTOR);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('joinDateFrom')) {
            $query->whereDate('created_at', '>=', $request->input('joinDateFrom'));
        }
        if ($request->filled('joinDateTo')) {
            $query->whereDate('created_at', '<=', $request->input('joinDateTo'));
        }

        $instructors = $query->orderBy('created_at', 'DESC')
            ->paginate($request->rowsPerPageOptions ?? 10);

        $pagination = $instructors->toArray();
        unset($pagination['data']);

        return response()->json([
            'data' => CustomerInfoResource::collection($instructors),
            'pagination' => $pagination,
        ]);
    }

    /**
     * Get students only
     */
    public function getStudents(Request $request)
    {
        $query = User::query()
            ->doesntHave('systemAdmin')
            ->where('role', STUDENT);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('joinDateFrom')) {
            $query->whereDate('created_at', '>=', $request->input('joinDateFrom'));
        }
        if ($request->filled('joinDateTo')) {
            $query->whereDate('created_at', '<=', $request->input('joinDateTo'));
        }

        $students = $query->orderBy('created_at', 'DESC')
            ->paginate($request->rowsPerPageOptions ?? 10);

        $pagination = $students->toArray();
        unset($pagination['data']);

        return response()->json([
            'data' => CustomerInfoResource::collection($students),
            'pagination' => $pagination,
        ]);
    }

    /**
     * Bulk import students from CSV
     */
    public function bulkImportStudents(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:csv,txt',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid file format. Please upload CSV file.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $file = $request->file('excel_file');
            $handle = fopen($file->getPathname(), 'r');

            if ($handle === false) {
                throw new Exception('Could not read the uploaded file.');
            }

            // Skip header row
            $header = fgetcsv($handle);

            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            $rowNumber = 2; // Starting from row 2 (after header)
            $errorTypes = [
                'validation' => 0,
                'duplicate' => 0,
                'format' => 0,
                'missing_data' => 0
            ];

            while (($row = fgetcsv($handle)) !== false) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    $rowNumber++;
                    continue;
                }

                try {
                    // Expected columns: first_name, middle_name, last_name, email, phone, gender
                    $firstName = trim($row[0] ?? '');
                    $middleName = trim($row[1] ?? '');
                    $lastName = trim($row[2] ?? '');
                    $email = trim($row[3] ?? '');
                    $phone = trim($row[4] ?? '');
                    $genderString = strtolower(trim($row[5] ?? ''));

                    // Validate required fields - only first name is required now
                    if (empty($firstName)) {
                        $errors[] = "Row {$rowNumber}: First name is required";
                        $errorTypes['missing_data']++;
                        $errorCount++;
                        $rowNumber++;
                        continue;
                    }

                    // Validate email format if provided, but don't auto-generate
                    $validatedEmail = null;
                    if (!empty($email)) {
                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            // Check if email already exists
                            if (User::where('email', $email)->exists()) {
                                $errors[] = "Row {$rowNumber}: Email {$email} already exists";
                                $errorTypes['duplicate']++;
                                $errorCount++;
                                $rowNumber++;
                                continue;
                            }
                            $validatedEmail = $email;
                        } else {
                            $errors[] = "Row {$rowNumber}: Invalid email format: {$email}";
                            $errorTypes['format']++;
                            $errorCount++;
                            $rowNumber++;
                            continue;
                        }
                    }

                    // Validate and format phone number if provided
                    $formattedPhone = null;
                    if (!empty($phone)) {
                        // Remove any non-digit characters except + at the beginning
                        $cleanPhone = preg_replace('/[^\d+]/', '', $phone);

                        // Store phone as string to preserve format
                        if (strlen($cleanPhone) >= 9) {
                            $formattedPhone = $cleanPhone;
                        } else {
                            $errors[] = "Row {$rowNumber}: Invalid phone number format: {$phone}";
                            $errorTypes['format']++;
                            $errorCount++;
                            $rowNumber++;
                            continue;
                        }
                    }

                    // Map gender string to integer values
                    $gender = null;
                    if ($genderString === 'male') {
                        $gender = MALE; // 1
                    } elseif ($genderString === 'female') {
                        $gender = FEMALE; // 2
                    }

                    // Create user
                    $user = new User();
                    $user->slug = Str::uuid();
                    $user->email = $validatedEmail; // Keep null if no valid email provided
                    $user->first_name = $firstName;
                    $user->middle_name = $middleName ?: null;
                    $user->last_name = $lastName ?: null;
                    $user->phone = $formattedPhone; // Store as string to preserve format
                    $user->gender = $gender;
                    $user->password = Hash::make('password123'); // Default password
                    $user->role = STUDENT;
                    $user->user_id = Auth::id();

                    // Only set email_verified_at if email exists
                    if ($validatedEmail) {
                        $user->email_verified_at = now();
                    }

                    $user->save();

                    // Create student relationship
                    $user->student()->create();

                    $successCount++;
                } catch (Exception $e) {
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                    $errorTypes['validation']++;
                    $errorCount++;
                }

                $rowNumber++;
            }

            fclose($handle);

            $this->adminActivities("Bulk imported {$successCount} students with {$errorCount} errors");

            DB::commit();

            // Prepare response message
            $message = '';
            if ($successCount > 0 && $errorCount === 0) {
                $message = "Import completed successfully. {$successCount} students imported.";
            } elseif ($successCount > 0 && $errorCount > 0) {
                $message = "Import completed with some issues. {$successCount} students imported, {$errorCount} rows had errors.";
            } else {
                $message = "Import failed. No students were imported due to data validation issues.";
            }

            return response()->json([
                'message' => $message,
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'total_processed' => $successCount + $errorCount,
                'errors' => array_slice($errors, 0, 50), // Show first 50 errors for debugging
                'error_summary' => [
                    'validation_errors' => $errorTypes['validation'],
                    'duplicate_emails' => $errorTypes['duplicate'],
                    'format_errors' => $errorTypes['format'],
                    'missing_required_data' => $errorTypes['missing_data']
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Import failed: ' . $e->getMessage(),
                'success_count' => 0,
                'error_count' => 0,
                'has_errors' => true
            ], 500);
        }
    }

    /**
     * Export instructors to CSV
     */
    public function exportInstructors(Request $request)
    {
        $query = User::query()
            ->doesntHave('systemAdmin')
            ->where('role', INSTRUCTOR);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $instructors = $query->orderBy('created_at', 'DESC')->get();
        return $this->exportToCSV($instructors, 'instructors');
    }

    /**
     * Export students to CSV
     */
    public function exportStudents(Request $request)
    {
        $query = User::query()
            ->doesntHave('systemAdmin')
            ->where('role', STUDENT);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('created_at', 'DESC')->get();
        return $this->exportToCSV($students, 'students');
    }

    /**
     * Export data to CSV format
     */
    private function exportToCSV($users, $type)
    {
        $filename = $type . '_export_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ];

        $callback = function () use ($users, $type) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            // Headers
            $headers = ['ID', 'First Name', 'Middle Name', 'Email', 'Phone', 'Status', 'Join Date'];
            if ($type === 'instructors') {
                $headers[] = 'Temp Password';
            }
            fputcsv($file, $headers);

            // Data
            foreach ($users as $user) {
                $row = [
                    $user->id,
                    $user->first_name,
                    $user->middle_name,
                    $user->email,
                    $user->phone,
                    $user->user_banned_at ? 'Blocked' : 'Active',
                    $user->created_at->format('Y-m-d H:i:s')
                ];

                if ($type === 'instructors') {
                    $row[] = $user->temp_password ?? 'Changed';
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
