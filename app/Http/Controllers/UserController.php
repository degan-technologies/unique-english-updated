<?php

namespace App\Http\Controllers;

use App\Http\Resources\Auth\CurrentUserResource;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Models\Role\Instructor;
use App\Models\Role\Student;
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
            ->has('systemAdmins')
            ->findOrFail(Auth::id());

        $user = User::findOrFail($id);
        $user->delete(); 

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
            'phone' => [ 'unique:users,phone,' . $user->id, 'regex:/[0-9]/', 'size:13'],
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
}
