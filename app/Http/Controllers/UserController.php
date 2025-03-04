<?php

namespace App\Http\Controllers;

use App\Http\Resources\Auth\CurrentUserResource;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\userResource;
use App\Models\Role\Instructor;
use App\Models\Role\Student;
use App\Models\Role\SystemAdmin;
use App\Services\LangService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller {

    /**
     * get error traslation and success beased on the language 
     * localized 
     */
    protected $langService;

    public function __construct(LangService $langService) {
        $this->langService = $langService;
    }

    public function index(Request $request)
    {
        // Start with all non-system-admin users who are not banned
        $query = User::query()
            ->where('role', '!=', SYSTEM_ADMIN)
            ->whereNull('user_banned_at');
    
        // Search filter: looks in first_name, middle_name, email, or role
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }
    
        // Role filter: exact match
        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }
    
        // Status filter: exact match (if a 'status' field exists)
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
    
        // Join date range filter using the created_at field as the join date
        if ($request->filled('joinDateFrom')) {
            $query->whereDate('created_at', '>=', $request->input('joinDateFrom'));
        }
        if ($request->filled('joinDateTo')) {
            $query->whereDate('created_at', '<=', $request->input('joinDateTo'));
        }
    
        // Progress filter: for students only, using the related student table
        if ($request->filled('progress')) {
            $progress = $request->input('progress');
            $query->where(function ($q) use ($progress) {
                // For non-students, ignore the progress filter
                $q->where('role', '!=', 'STUDENT_ROLE')
                  // For students, use the student relation
                  ->orWhereHas('student', function ($q2) use ($progress) {
                      $q2->where('progress', '>=', $progress);
                  });
            });
        }
    
        // Optional: if you want to paginate, you could do:
        // $users = $query->paginate($request->input('perPage', 10));
        $users = $query->get();
    
        return response()->json([
             'data' => \App\Http\Resources\userResource::collection($users)
        ]);
    }
    


    /**
     * Store a newly created resource in storage.
     *  @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function store(Request $request) {
        $validationRules = [
            'email' => 'required|email|unique:users',
            'first_name' => ['required', 'not_regex:/[\\\\\/\?\%\*\:\|\"<>]/', 'alpha_dash:ascii'],
            'middle_name' => ['not_regex:/[\\\\\/\?\%\*\:\|\"<>]/', 'alpha_dash:ascii'],
            'password' => 'required|min:4',
        ];

        $validator = Validator::make($request->all(), $validationRules,  $this->langService->getLang('registration'));
        
        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }

        try{
            DB::beginTransaction();
            $user = new User();
            $user->slug = Str::uuid();
            $user->email = $request->email;
            $user->first_name = $request->first_name;
            $user->middle_name = $request->middle_name;
            $user->password = Hash::make($request->password);
            $user->role = STUDENT;
            $user->created_at = Carbon::now();
            $user->save();

            $student = new Student();
            $student->user_id = $user->id;
            $student->save();
            DB::commit();
        } catch( Exception $e) {
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
     * Update the specified resource in storage.
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * 
     * @return \Illuminate\Http\Response
     */
    public function addInstructor(Request $request) {
        // Ensure that the current user is allowed to add an instructor
        $canAddinstructor = User::query()
            ->has('systemAdmin')
            ->findOrFail(Auth::id());
    
        // Validation rules without a password field
        $validationRules = [
            'email'      => 'required|email|unique:users',
            'first_name' => ['required', 'not_regex:/[\\\\\/\?\%\*\:\|\"<>]/', 'alpha_dash:ascii'],
            'middle_name'=> ['not_regex:/[\\\\\/\?\%\*\:\|\"<>]/', 'alpha_dash:ascii'],
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
            
            // Generate a random password and hash it
            $randomPassword = Str::random(8); // Adjust length as needed
            $user->password = Hash::make($randomPassword);
            
            // Store the plain text password temporarily for display purposes.
            // Make sure you have a 'temp_password' column in your 'users' table.
            $user->temp_password = $randomPassword;
            
            // Default role is Instructor
            $user->role = INSTRUCTOR;
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

    public function addStudent(Request $request) {
        $canAddinstructor = User::query()
            ->has('systemAdmin')
            ->findOrFail(Auth::id());


        $validationRules = [
            'email' => 'required|email|unique:users',
            'first_name' => ['required', 'not_regex:/[\\\\\/\?\%\*\:\|\"<>]/', 'alpha_dash:ascii'],
            'middle_name' => ['not_regex:/[\\\\\/\?\%\*\:\|\"<>]/', 'alpha_dash:ascii'],
            'password' => 'required|min:4'
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
    public function destroy($id) {
        $canDeleteUser = User::query()
            ->has('systemAdmin')
            ->findOrFail(Auth::id());

        $user = User::findOrFail($id);
        $user->update([
            'user_banned_at'=> Carbon::now(),
        ]);

        return response()->json([
            'message' =>$this->langService->getLang('user_successfully_deleted')
        ]);
    }

    /**
     * update profile
     */
    public function profileUpdate(Request $request) {
        /**
         * @var \App\Models\User $user
         */
        $user = Auth::user();

        $validationRules = [
            'email' => 'required|email|unique:users,email,' . $user->id,
            'first_name' => ['not_regex:/[\\\\\\/\\?\\%\\*\\:\\|\"<>]/', 'alpha_dash:ascii'],
            'middle_name' => ['not_regex:/[\\\\\\/\\?\\%\\*\\:\\|\"<>]/', 'alpha_dash:ascii'],
            'last_name' => ['not_regex:/[\\\\\\/\\?\\%\\*\\:\\|\"<>]/', 'alpha_dash:ascii'],
            'phone' => [ 'unique:users,phone,' . $user->id, 'regex:/^\+[1-9]\d{1,14}$/' ],
            'profile' => 'image',
            'bg_image' => 'image',
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

         $profilePath = null;
         $bgPath = null;

        if ($request->hasFile('profile')) {
            $file = $request->file('profile');
            $profilePath = $file->store('/user', 'public');
        }

        if ($request->hasFile('bg_image')) {
            $file = $request->file('bg_image');
            $bgPath = $file->store('/user', 'public');
        }

        $user->update([
            'email' =>$request->email ?? $user->email,
            'phone' => $request->phone ?? $user->phone,
            'gender' =>$request->gender ?? $user->gender,
            'first_name' =>$request->first_name ?? $user->first_name ,
            'middle_name' => $request->middle_name ?? $user->middle_name,
            'last_name' => $request->last_name ?? $user->last_name,
            'profile' => $profilePath ?? $user->profile,
            'bg_image' => $bgPath ?? $user->bg_image,
            'updated_at' => Carbon::now(),
        ]);

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
    public function passwordReset(Request $request) {

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

    // Update each selected user's 'user_banned_at' field to mark them as "deleted"
    User::whereIn('id', $userIds)->update([
        'user_banned_at' => Carbon::now()
    ]);

    return response()->json([
        'message' => $this->langService->getLang('user_successfully_deleted')
    ]);
}





}
