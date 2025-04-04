<?php

namespace App\Http\Controllers\Transaction\Trait;

use App\Http\Resources\Bank\TransferResource;
use App\Models\Bank\BankInfo;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\Transfer;
use App\Models\User; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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


    public function systemTransaction(Request $request) {
        $user = User::query()
            ->has('systemAdmin')
            ->first();

        if (!$user) return;

        /**
         * @var \Illuminate\Pagination\LengthAwarePaginator $transactions
         */

        $activeFilter = $request->activeFilter ?? null; 

        $transactions = Transaction::query()
            ->where('status', TRANSACTION_SUCCESS)
            ->get();

        $totalSell = $transactions
            ->sum('amount');

        $transactionToday = $transactions
            ->where('created_at', '>=', Carbon::today())
            ->sum('amount');

        $transactionThisMonth = $transactions
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->sum('amount');

        switch ($activeFilter) {
            case TODAY:
                $activeFilter = $transactions->where('created_at', '>=', Carbon::today());
                break;
            case THIS_MONTH:
                $activeFilter = $transactions->where('created_at', '>=', Carbon::now()->startOfMonth());
                break;
            default:
                $activeFilter = $transactions;
                break;
        }

        $courseSell = $activeFilter->where('product_type', COURSE)
            ->sum('amount');

        $bookSell = $activeFilter->where('product_type', BOOK)
            ->sum('amount');

        $liveSell = $activeFilter->where('product_type', LIVE)
            ->sum('amount');

        return response()->json([
            'courseSell' => $courseSell,
            'bookSell' => $bookSell,
            'liveSell' => $liveSell,

            'totalSell' => $totalSell,
            'transactionToday' => $transactionToday,
            'transactionThisMonth' => $transactionThisMonth,
        ]);
    }
}