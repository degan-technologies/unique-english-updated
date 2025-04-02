<?php

namespace App\Http\Controllers\Transaction\Trait;

use App\Http\Resources\Bank\TransferResource;
use App\Models\Bank\BankInfo;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\Transfer;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

trait TransferTrait {
    public function transferHistory($transaction, $status, $totalPrice = null) {

        $bankInfo = BankInfo::query()
            ->where('user_id', $transaction->user_id)
            ->first();

        $transfer= Transfer::create([
            'account_number' => $bankInfo->account_number,
            'amount' => $totalPrice,
            'currency' => 'ETB',
            'reference' => $transaction->reference ?? $transaction->tx_ref,
            'narration' => 'Transfer to bank account',
            'status' => $status,
            'user_id' =>Auth::id(),
            'transaction_id' => $transaction->id ?? null
        ]);

        return;
    }


    public function getTransferHistory() {

        $transferInfo = Transfer::query()
            ->where('user_id', Auth::id() )
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => TransferResource::collection($transferInfo)
        ]);
    }

    public function getBalance() {
        
        $balancehistory = Transfer::query()
            ->where('user_id', Auth::id() )
            ->get();

        $withdrawal = $balancehistory->where('status', WITHDRAWAL)->sum('amount');
        $deposit = $balancehistory->where('status', DEPOSIT)->sum('amount');

        $balance = $deposit - $withdrawal;

        return response()->json([
            'status' => 'success',
            'data' => $balance
        ]);
    }
}