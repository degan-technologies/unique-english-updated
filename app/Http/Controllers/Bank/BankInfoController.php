<?php

namespace App\Http\Controllers\Bank;

use App\Http\Controllers\Controller;
use App\Http\Resources\Bank\BankInfoResource;
use App\Models\Bank\BankInfo;
use App\Models\User;
use App\Services\LangService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BankInfoController extends Controller {
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
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$user) return;

        $validationRules = [
            'full_name' => 'required',
            'bank_name' => 'required',
            'bank_code' => 'required',
            'account_number' => 'required',
            'password' => 'required',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('banksInfos'));

        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->all()[0],
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => $this->langService->getLang('incorrect_password')
            ], 422);
        }
 
        $bank = $user->bankInfos()->create([
            'full_name' => $request->full_name,
            'bank_name' => $request->bank_name,
            'bank_code' => $request->bank_code,
            'account_number' => $request->account_number,
        ]);

        return response()->json([
            'message' => $this->langService->getLang('bank_info_created'),
            'data' => new BankInfoResource($bank),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$user) return;

        $bankInfo = BankInfo::query()
            ->where('user_id', $user->id)
            ->findOrFail($id);

        if (!$bankInfo) {
            return response()->json([
                'message' => $this->langService->getLang('bankInfo_not_found'),
            ], 404);
        }

        $validationRules = [
            'full_name' => 'required',
            'bank_name' => 'required',
            'bank_code' => 'required',
            'account_number' => 'required',
            'password' => 'required',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('banksInfos'));

        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->all()[0],
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => $this->langService->getLang('incorrect_password')
            ], 422);
        }

        $bankInfo->update([
            'full_name' => $request->full_name,
            'bank_name' => $request->bank_name,
            'bank_code' => $request->bank_code,
            'account_number' => $request->account_number,
        ]);

        return response()->json([
            'message' => $this->langService->getLang('bank_info_updated'),
            'data' => new BankInfoResource($bankInfo)
        ]);
    }

    /**
     * fetch bank info for the authenticated user
     */

     public function myBankInfo() {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$user) return;

        $bankInfo = BankInfo::query()
            ->where('user_id', $user->id)
            ->first();

        if (!$bankInfo) {
            return response()->json([
                'message' => $this->langService->getLang('bankInfo_not_found'),
            ], 404);
        }

        return response()->json([
            'data' => new BankInfoResource($bankInfo)
        ]);
     }
}
