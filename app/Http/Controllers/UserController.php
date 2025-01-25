<?php

namespace App\Http\Controllers;

use App\Helper\Lang\ErrorLang;
use App\Helper\Type\Gender\Gender;
use App\Http\Resources\Auth\CurrentUserResource;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
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
            $user->save();
            $user->created_at = Carbon::now();

            $student = new Student();
            $student->user_id = $user->id;
            $student->save();

            DB::beginTransaction();
        } catch( Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e,
            ], 500);
        }
        return response()->json([
            'message' => $this->langService->getLang('user_successfully_registered'),
            'data' => new UserResource($user),
        ]);
    }
}
