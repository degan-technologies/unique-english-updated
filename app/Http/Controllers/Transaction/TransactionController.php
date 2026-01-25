<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Notifications\Trait\CreateNotificationTrait;
use App\Http\Controllers\Transaction\Trait\TransferTrait;
use App\Http\Resources\Bank\TransactionHistoryResource;
use App\Models\Book\Book;
use App\Models\Course\Course;
use App\Models\Plan\Plan;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\Transfer;
use App\Models\User;
use App\Services\ChapaService;
use App\Services\LangService;
use App\Traits\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    use TransferTrait, AdminActivityLog, CreateNotificationTrait;

    protected $langService;
    protected $chapaService;

    public function __construct(LangService $langService, ChapaService $chapaService)
    {
        $this->langService = $langService;
        $this->chapaService = $chapaService;
    }

    public function handleTransferApproval(Request $request)
    {

        $response = $this->chapaService->verifyWebhook($request);

        if (!$response) {
            Log::warning('Invalid Chapa transfer approval attempt', [
                'ip' => $request->ip(),
                'headers' => $request->headers->all(),
                'data' => $request->all()
            ]);
            return response()->json(['message' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $payload = $request->all();

        if (!isset($payload['tx_ref']) || !isset($payload['status'])) {
            Log::warning('Required fields missing in webhook payload', ['payload' => $payload]);
            return response()->json(['message' => 'Bad payload'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // DB::beginTransaction();

            $transactions = Transaction::query()
                ->where('tx_ref', $payload['tx_ref'])
                ->get();

            if ($transactions->isEmpty()) {
                Log::warning('Transfer not found for tx_ref: ' . $payload['tx_ref']);
                return response()->json(['message' => 'Transfer not found'], Response::HTTP_NOT_FOUND);
            }

            $status = strtolower($payload['status']) === 'success' ? 'success' : 'failed';

            $transactions->each(function ($transaction) use ($status) {
                $transaction->update([
                    'status' => $status,
                    'updated_at' => now(),
                ]);

                if ($transaction->transfer) {
                    $transaction->transfer()->update(['status' => $status]);
                }

                if ($transaction->status === 'success') {
                    $getresponse = $this->enrollmentNotification($transaction->product_type, $transaction);
                }
            });

            DB::commit();

            return response()->json(['message' => 'Transfer processed'], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('Transfer approval processing failed: ' . $e->getMessage(), ['payload' => $payload]);
            return response()->json(['message' =>  $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function handleWithdrawalApproval(Request $request)
    {
        return response()->json(['message' => 'Transfer approved'], 200);
    }

    public function initiatePayment(Request $request)
    {
        $user = Auth::user();
        $cartItems = $request->cartItems ?? [];
        $txRef = 'TX-' . uniqid();
        $totalPrice = 0;

        if (empty($cartItems)) {
            return response()->json([
                'message' => $this->langService->getLang('cart_empty')
            ], Response::HTTP_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'cartItems.*' => ['required', 'array'],
            'cartItems.*.type' => ['required', Rule::in(ORDER_TYPES)],
            'cartItems.*.slug' => ['required', 'string'],
        ], $this->langService->getLang('transactions'));

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            foreach ($cartItems as $item) {
                $model = $this->getModelByType($item['type']);

                $order = $model->where('slug', $item['slug'])->firstOrFail();
                $price = $this->getPriceForItem($order, $item);
                $totalPrice += $price;

                $this->createOrUpdateTransaction(
                    $order,
                    $user,
                    $item,
                    $txRef,
                    $price
                );
            }

            $paymentData = $this->preparePaymentData($user, $txRef, $totalPrice);
            $response = $this->chapaService->initializePayment($paymentData);

            if (!isset($response['status'])) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Payment gateway error'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $this->adminActivities($user->full_name . ' enrolled at ' . now()->format('Y-m-d H:i:s') .
                ' with transaction ID: ' . $txRef);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => $this->langService->getLang('payment_initiated'),
            'checkout_url' => $response['data']['checkout_url'],
        ]);
    }

    public function transactions(Request $request)
    {
        $user = User::query()
            ->where('id', Auth::id())
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $baseQuery = Transaction::query()->orderByDesc('created_at');

        if (!$user->systemAdmin()->exists()) {
            $baseQuery->where('user_id', $user->id);
        }

        $transactions = (clone $baseQuery)->paginate($request->rowsPerPageOptions);

        $allRows = (clone $baseQuery)->get();

        $transactionData = $this->prepareTransactionData(
            $allRows,
            $request->summaryLength === 'true'
        );

        $pagination = $transactions->toArray();
        unset($pagination['data']);

        return response()->json([
            'data'        => TransactionHistoryResource::collection($transactions),
            'pagination'  => $pagination,
            ...$transactionData,
        ]);
    }

    public function getBankList()
    {
        return response()->json([
            'data' => $this->chapaService->getBankList()
        ]);
    }
    public function transferToBank(Request $request)
    {
        /**
         * @var mixed $user
         */
        $user = Auth::user();
        $txRef = 'Trf-' . uniqid();

        $validator = Validator::make(
            $request->all(),
            ['amount' => ['required', 'numeric', 'min:1']],
            $this->langService->getLang('transfers')
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first()
            ], 422);
        }

        $bankInfo = $user->bankInfos()->firstOrFail();
        $balance = $this->getUserBalance($user);

        if ($balance < $request->amount) {
            return response()->json([
                'message' => $this->langService->getLang('insufficient_balance')
            ], 400);
        }

        $data = [
            'account_number' => $bankInfo->account_number,
            'bank_code' => $bankInfo->bank_code,
            'amount' => $request->amount,
            'currency' => 'ETB',
            'reference' => $txRef,
            'callback_url' => route('chapa.transfer.callback'),
            'approval_url' => route('transfer.approval'),
        ];


        DB::beginTransaction();

        try {
            $transfer = $this->createWithdraw($request->amount, $txRef);

            $response = $this->chapaService->transfer($data);

            if (!isset($response['status']) || $response['status'] !== 'success') {
                $message = $response['message'] ?? 'Transfer initiation failed';
                $errors = $response['errors'] ?? null;

                DB::rollBack();

                return response()->json([
                    'message' => $message,
                    'errors' => $errors,
                    'gateway_response' => $response
                ], 400);
            }

            $chapaReference = $response['data'] ? $response['data']['reference'] : $response['data'];

            if (empty($chapaReference)) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Payment gateway returned invalid reference',
                    'gateway_response' => $response
                ], 400);
            }

            $transfer['transfer']->update([
                'chapa_reference' => $chapaReference,
                'status' => $response['status'],
            ]);

            $transfer['commission']->update([
                'status' => $response['status'],
            ]);

            DB::commit();

            $this->adminActivities('Transfer initiated by ' . $user->full_name . ' with transaction ID: ' . $txRef);

            return response()->json([
                'transfer' => $transfer['transfer'],
                'response' => $response,
                'message' => 'Transfer initiated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'An unexpected error occurred during transfer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkTransferStatus()
    {
        $response = Http::withToken(config('services.chapa.secret_key'))
            ->get("https://api.chapa.co/v1/transfer/events/CTzAUrTZ1yVVm2");

        return response()->json($response->json());
    }

    // Helper methods

    private function getModelByType(string $type)
    {
        return match ($type) {
            COURSE => new Course,
            BOOK => new Book,
            LIVE_CLASS => new Plan,
            default => throw new \InvalidArgumentException('Invalid order type'),
        };
    }

    private function getPriceForItem($order, array $item): float
    {
        if ($item['type'] === LIVE_CLASS) {
            return $item['live_price_type'] === 'individual'
                ? $order->one_to_one_price
                : $order->group_price;
        }

        return $order->price;
    }

    private function createOrUpdateTransaction($order, $user, $item, $txRef, $price)
    {
        $existingTransaction = $order->transactions()
            ->where('customer_id', $user->id)
            ->where('product_type', $item['type'])
            ->where('live_price_type', $item['live_price_type'] ?? 'notLive')
            ->first();

        if ($existingTransaction) {
            if ($existingTransaction->status === TRANSACTION_SUCCESS) {
                throw new \Exception($this->langService->getLang('aleready exist'));
            }

            $existingTransaction->update([
                'tx_ref' => $txRef,
                'amount' => $price,
                'enrolled_at' => now()
            ]);

            $this->createDeposit($existingTransaction);
        } else {
            $transaction = $order->transactions()->create([
                'slug' => Str::uuid(),
                'user_id' => $order->user_id,
                'tx_ref' => $txRef,
                'amount' => $price,
                'customer_id' => $user->id,
                'status' => TRANSACTION_PENDING,
                'product_type' => $item['type'],
                'live_price_type' => $item['live_price_type'] ?? 'notLive',
                'enrolled_at' => now()
            ]);

            $this->createDeposit($transaction);
        }
    }

    private function preparePaymentData($user, $txRef, $totalPrice): array
    {
        return [
            'amount' => $totalPrice,
            'email' => $user->email,
            'currency' => "ETB",
            'first_name' => $user->first_name,
            'last_name' => $user->first_name,
            'tx_ref' => $txRef,
            "customization" => [
                "title" => $this->langService->getLang('unique_english_payment_transaction'),
                "description" => $this->langService->getLang('my_payment_description') . 29392,
            ]
        ];
    }

    private function prepareTransactionData($transactions, $monthlySummary = false): array
    {
        $successful = $transactions->where('status', TRANSACTION_SUCCESS);

        $courseSell = $successful->where('product_type', COURSE)->sum('amount');
        $bookSell   = $successful->where('product_type', BOOK)->sum('amount');
        $liveSell   = $successful->where('product_type', LIVE_CLASS)->sum('amount');
        $totalSell  = $successful->sum('amount');

        $transactionSummary = $successful
            ->groupBy(
                fn($t) =>
                $monthlySummary
                    ? $t->created_at->format('Y-m')
                    : $t->created_at->format('Y-m-d') // e.g. "2025-06-13"
            )
            ->map->sum('amount')
            ->all();

        return [
            'courseSell'          => $courseSell,
            'bookSell'            => $bookSell,
            'liveSell'            => $liveSell,
            'totalSell'           => $totalSell,
            'transactionSummary'  => $transactionSummary,
            'transactionToday'    => $successful->where('created_at', '>=', today())->sum('amount'),
            'transactionThisMonth' => $successful->where('created_at', '>=', now()->startOfMonth())->sum('amount'),
        ];
    }

    private function getUserBalance(User $user): float
    {
        return Transfer::where('user_id', $user->id)
            ->where('status', TRANSACTION_SUCCESS)
            ->sum('deposits')
            - Transfer::where('user_id', $user->id)
            ->where('status', TRANSACTION_SUCCESS)
            ->sum('withdrawals');
    }

    /**
     * Export transactions to CSV
     */
    public function exportTransactions(Request $request)
    {
        $user = User::query()
            ->where('id', Auth::id())
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $baseQuery = Transaction::query()
            ->with(['customer'])
            ->orderByDesc('created_at');

        if (!$user->systemAdmin()->exists()) {
            $baseQuery->where('user_id', $user->id);
        }

        // Apply filters if provided
        if ($request->filled('status')) {
            $baseQuery->where('status', $request->status);
        }
        if ($request->filled('startDate')) {
            $baseQuery->whereDate('created_at', '>=', $request->startDate);
        }
        if ($request->filled('endDate')) {
            $baseQuery->whereDate('created_at', '<=', $request->endDate);
        }

        $transactions = $baseQuery->get();

        return $this->exportTransactionsToCSV($transactions);
    }

    /**
     * Export transactions data to CSV format
     */
    private function exportTransactionsToCSV($transactions)
    {
        $filename = 'transactions_export_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            // Headers
            $headers = [
                'Transaction ID',
                'Customer Name',
                'Email',
                'Phone',
                'Product Type',
                'Amount (ETB)',
                'Status',
                'Date',
                'Reference'
            ];
            fputcsv($file, $headers);

            // Data
            foreach ($transactions as $transaction) {
                $customerName = $transaction->customer
                    ? $transaction->customer->first_name . ' ' . $transaction->customer->middle_name
                    : 'N/A';

                $row = [
                    $transaction->id,
                    $customerName,
                    $transaction->customer->email ?? 'N/A',
                    $transaction->customer->phone ?? 'N/A',
                    ucfirst($transaction->product_type),
                    number_format($transaction->amount, 2),
                    ucfirst($transaction->status),
                    $transaction->created_at->format('Y-m-d H:i:s'),
                    $transaction->tx_ref
                ];

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
