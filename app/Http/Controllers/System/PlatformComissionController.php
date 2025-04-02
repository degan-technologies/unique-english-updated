<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LangService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PlatformComissionController extends Controller {
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
     */
    public function store(Request $request) {
        $user = User::query()
            ->has('systemAdmin')
            ->where('id', Auth::id())
            ->first();


        $validationRules = [
            'fees' => 'required|numeric',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('comission'));

        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->all()[0],
                'errors' => $validator->errors()
            ], 422);
        }
    
        $platformComission = $user->platformComissions()->first();

        $fee = $request->fees / 100 ?? 0.00;

        try{
            DB::beginTransaction();
                if (!$platformComission) {
                    $platformComission = $user->platformComissions()->create([
                        'fees' =>  $fee,
                        'created_at' => Carbon::now(),
                    ]);

                    $this->storeHistory($platformComission);
                    DB::commit();

                    return response()->json([
                        'message' => $this->langService->getLang('comission_added'),
                        'data' => $platformComission
                    ]);
                }

                $platformComission->update([
                    'fees' =>  $fee,
                    'updated_at' => Carbon::now(),
                ]);

                $this->storeHistory($platformComission);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return response()->json([
                'message' => $this->langService->getLang('comission_error')
            ], 422);
        }

        return response()->json([
            'message' => $this->langService->getLang('comission_updated'),
            'fees' => $platformComission->fees,
            'created_at' => $platformComission->created_at,
            'updated_at' => $platformComission->updated_at,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function getComission() {
        $platformComission = User::query()
            ->has('systemAdmin')
            ->where('id', Auth::id())
            ->first()
            ->platformComissions()
            ->first();

        return response()->json([
            'status' => 'success',
            'fees' => $platformComission->fees,
            'created_at' => $platformComission->created_at,
            'updated_at' => $platformComission->updated_at,
        ]);
    }

    /**
     * store commsion history
     */

    public function storeHistory($platformComission) {
        $platformComission->platformComissionHistories()->create([
            'fees' => $platformComission->fees,
            'user_id' => $platformComission->user_id,
            'created_at' => Carbon::now(),
        ]);
    }

}
