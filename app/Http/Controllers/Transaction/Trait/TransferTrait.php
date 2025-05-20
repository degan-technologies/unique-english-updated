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
    public function createDeposit($transaction) {

        $balance = Transfer::query()
            ->where('user_id', $transaction->user_id)
            ->where('status', TRANSACTION_SUCCESS)
            ->orderBy('id', 'desc')
            ->first()->balance ?? 0;

        $balance = $balance + $transaction->amount;

        if ($transaction->transfer) {
            $transaction->transfer()->update([
                'deposits' => $transaction->amount,
                'user_id' => $transaction->user_id,
                'transaction_id' => $transaction->id ?? null,
                'balance' => $balance,
            ]);
            return;
        }

        $transaction->transfer()->create([  
            'deposits' => $transaction->amount, 
            'user_id' =>$transaction->user_id,
            'transaction_id' => $transaction->id ?? null,
            'balance' => $balance,
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

        $balance = Transfer::query()
            ->where('user_id', Auth::id())
            ->where('status', TRANSACTION_SUCCESS)
            ->orderBy('id', 'desc')
            ->first()->balance ?? 0;

        return response()->json([
            'status' => 'success',
            'data' => $balance
        ]);
    }

    public function createWithdraw($amount, $trf) { 
        $user = User::query()
            ->where('id', Auth::id())
            ->first();
        if (!$user) return;

        $balance = Transfer::query()
            ->where('user_id', $user->id)
            ->where('status', TRANSACTION_SUCCESS)
            ->sum('deposits')
            - Transfer::query()
            ->where('user_id', $user->id)
            ->where('status', TRANSACTION_SUCCESS)
            ->sum('withdrawals');

         
       $transfer = Transfer::create([
            'withdrawals' => $amount,
            'user_id' => $user->id,
            'balance' => $balance,
            'reference' => $trf,
        ]);

        return $transfer;
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