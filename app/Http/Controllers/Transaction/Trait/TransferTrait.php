<?php

namespace App\Http\Controllers\Transaction\Trait;

use App\Http\Resources\Bank\TransactionHistoryResource;
use App\Http\Resources\Bank\TransferResource;
use App\Models\Bank\BankInfo;
use App\Models\Comment\FeedBack;
use App\Models\System\PlatformComission;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\Transfer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\CloudFrontService;

trait TransferTrait
{
    public function createDeposit($transaction)
    {

        $balance = $balance = Transfer::query()
            ->where('user_id', $transaction->user_id)
            ->where('status', TRANSACTION_SUCCESS)
            ->sum('deposits')
            - Transfer::query()
            ->where('user_id', $transaction->user_id)
            ->where('status', TRANSACTION_SUCCESS)
            ->sum('withdrawals');;

        $balance += $transaction->amount;

        if ($transaction->transfer) {
            $transaction->transfer()->update([
                'deposits' => $transaction->amount,
                'user_id' => $transaction->user_id,
                'transaction_id' => $transaction->id ?? null,
                'reference' => $transaction->tx_ref,
                'balance' => $balance,
            ]);
            return;
        }

        $transaction->transfer()->create([
            'deposits' => $transaction->amount,
            'user_id' => $transaction->user_id,
            'transaction_id' => $transaction->id ?? null,
            'reference' => $transaction->tx_ref,
            'balance' => $balance,
        ]);

        return;
    }

    public function getTransferHistory(Request $request)
    {

        $transferInfo = Transfer::query()
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'DESC')
            ->paginate($request->rowsPerPageOptions);

        $pagination = $transferInfo->toArray();
        unset($pagination['data']);

        return response()->json([
            'status' => 'success',
            'data' => TransferResource::collection($transferInfo),
            'pagination' => $pagination,
        ]);
    }

    public function getBalance() { 
        $balance = $this->chapaService->getBalance();
        $availableBalance = $balance['data'][0]['available_balance'] ?? 0;

        return response()->json([
            'status' => 'success',
            'data' => $availableBalance,
            'currency' => $balance['data'][0]['currency'] ?? 'ETB',
        ]);
    }

    public function createWithdraw($amount, $trf)
    {
        $user = User::query()
            ->where('id', Auth::id())
            ->where(function ($q) {
                $q->whereHas('instructor')
                    ->orWhereHas('systemAdmin');
            })
            ->first();

        if (!$user) {
            return [
                'status' => 'failed',
                'message' => 'Unauthorized',
            ];
        }


        $platformComission = PlatformComission::first()->fees;
        $commission = 0;

        if ($platformComission && $platformComission > 0 && $user->instructor) {
            $commission = $amount * $platformComission;
        }

        $balance = $this->chapaService->getBalance();
        $availableBalance = $balance['data'][0]['available_balance'] ?? 0;

        if ($availableBalance < $amount || $availableBalance < 5) {
            return [
                'status' => 'failed',
                'message' => 'Insufficient balance to make a withdrawal. Minimum balance required is 5 ETB.',
            ];
        }

        try {
            DB::beginTransaction();

            $transaction = Transaction::create([
                'amount' => $amount,
                'commission' => $commission,
                'transaction_type' => WITHDRAWAL,
                'status' => TRANSACTION_PENDING,
                'payment_method' => TRANSFER,
                'product_type' => null,
                'tx_ref' => $trf,
                'user_id' => $user->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status' => 'failed',
                'message' => 'An error occurred while processing the withdrawal.',
                'error' => $e->getMessage(),
            ];
        }

        DB::commit();

        return [
            'status' => 'success', 
            'transaction' => $transaction,
        ];
    }


    public function systemTransaction(Request $request)
    {
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

    public function getTopSeller()
    {

        $topUsers = Transaction::query()
            ->where('status', TRANSACTION_SUCCESS)
            ->select('user_id')
            ->selectRaw('COUNT(*) as transaction_count')
            ->groupBy('user_id')
            ->orderByDesc('transaction_count')
            ->limit(10)
            ->get();

        $users = User::query()
            ->whereIn('id', $topUsers->pluck('user_id'))
            ->with(['transactions' => function ($query) {
                $query->where('status', TRANSACTION_SUCCESS)
                    ->select('user_id', 'amount', 'product_type');
            }])
            ->get()
            ->map(function ($user) use ($topUsers) {
                return [
                    'first_name' => $user->first_name,
                    'middle_name' => $user->middle_name,
                    'email' => $user->email,
                    'transaction_count' => $topUsers->firstWhere('user_id', $user->id)->transaction_count,
                    'amount' => $user->transactions->sum('amount'),
                ];
            });

        if (!$users) {
            return response()->json([
                'data' => [],
            ]);
        }

        return response()->json([
            'data' => $users,
        ]);
    }
    public function topSoldCourses()
    {
        $topCourses = Transaction::query()
            ->where('status', TRANSACTION_SUCCESS)
            ->where('product_type', COURSE)
            ->with(['course:id,course_name,thumbnail_url,price'])
            ->select('course_id')
            ->selectRaw('COUNT(*) as transaction_count')
            ->selectRaw('SUM(amount) as total_revenue')
            ->groupBy('course_id')
            ->orderByDesc('transaction_count')
            ->limit(5)
            ->get()
            ->map(function ($transaction) {
                $thumbnailUrl = $transaction->course->thumbnail_url ?? null;
                return [
                    'course_id' => $transaction->course_id,
                    'course_name' => $transaction->course->course_name ?? 'Deleted Course',
                    'thumbnail_url' => $thumbnailUrl ? CloudFrontService::signedUrl($thumbnailUrl, now()->addMinutes(30)) : 'images/no-profile.png',
                    'price' => $transaction->course->price ?? 0,
                    'transaction_count' => $transaction->transaction_count,
                    'total_revenue' => $transaction->total_revenue,
                    'average_rating' => FeedBack::reviewRate($transaction->course->feedbacks) ?? 0,
                ];
            });

        // Get monthly sales data for chart
        $monthlySales = Transaction::query()
            ->where('status', TRANSACTION_SUCCESS)
            ->where('product_type', COURSE)
            ->selectRaw('YEAR(created_at) as year')
            ->selectRaw('MONTH(created_at) as month')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(amount) as revenue')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        if (!$topCourses) {
            return response()->json([
                'data' => [],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'top_courses' => $topCourses,
                'monthly_sales' => $monthlySales,
            ],
        ]);
    }

    public function topSoldBooks()
    {
        $topBooks = Transaction::query()
            ->where('status', TRANSACTION_SUCCESS)
            ->where('product_type', BOOK)
            ->with(['book:id,title,cover_page_url,price'])
            ->select('book_id')
            ->selectRaw('COUNT(*) as transaction_count')
            ->selectRaw('SUM(amount) as total_revenue')
            ->groupBy('book_id')
            ->orderByDesc('transaction_count')
            ->limit(5)
            ->get()
            ->map(function ($transaction) {
                $coverPageUrl = $transaction->book->cover_page_url ?? null;
                return [
                    'book_id' => $transaction->book_id,
                    'title' => $transaction->book->title ?? 'Deleted Book',
                    'cover_page_url' => $coverPageUrl ? CloudFrontService::signedUrl($coverPageUrl, now()->addMinutes(30)) : 'images/no-profile.png',
                    'price' => $transaction->book->price ?? 0,
                    'transaction_count' => $transaction->transaction_count,
                    'total_revenue' => $transaction->total_revenue,
                    'average_rating' => FeedBack::reviewRate($transaction->book->feedbacks ?? collect()) ?? 0,
                ];
            });

        // Get monthly sales data for chart
        $monthlySales = Transaction::query()
            ->where('status', TRANSACTION_SUCCESS)
            ->where('product_type', BOOK)
            ->selectRaw('YEAR(created_at) as year')
            ->selectRaw('MONTH(created_at) as month')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(amount) as revenue')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        if (!$topBooks) {
            return response()->json([
                'data' => [],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'top_books' => $topBooks,
                'monthly_sales' => $monthlySales,
            ],
        ]);
    }
}