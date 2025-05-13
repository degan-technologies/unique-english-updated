<?php

namespace App\Http\Controllers\Transaction;


use App\Http\Controllers\Controller;
use App\Http\Controllers\Transaction\Trait\TransferTrait;
use App\Http\Resources\Bank\TransactionHistoryResource;
use App\Notifications\TransactionSuccessfulNotification;

use App\Models\Book\Book;
use App\Models\Course\Course; 
use App\Models\Plan\Plan;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\Transfer;
use App\Models\User;
use App\Services\ChapaService;
use App\Services\LangService;
use Carbon\Carbon; 
use App\Traits\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TransactionController extends Controller {

    use TransferTrait;

    /* get error traslation and success beased on the language 
     * localized 
     * and
     * Initialize Rave payment process
     * @return void
     */
    protected $langService;
    protected $chapaService;
    use AdminActivityLog ;

    public function __construct(LangService $langService, ChapaService $chapaService) {
        $this->langService = $langService;
        $this->chapaService = $chapaService;
    }

    public function handleTransferApproval(Request $request) {
        // Verify the incoming request from Chapa
        $isValid = $this->chapaService->verifyWebhook($request);

        if (!$isValid) {
            Log::warning('Invalid Chapa transfer approval attempt', [
                'ip' => $request->ip(),
                'data' => $request->all()
            ]);

            return response()->json(['message' => 'Invalid request'], 400);
        }

        $transferData = $request->all();

        // Process the transfer approval
        try {
            DB::beginTransaction();

            // Find the transfer by reference
            $transfer = Transfer::where('reference', $transferData['reference'])->first();

            if (!$transfer) {
                return response()->json(['message' => 'Transfer not found'], 404);
            }

            // Update transfer status based on Chapa's response
            $transfer->update([
                'status' => $transferData['status'] === 'success' ? TRANSACTION_SUCCESS : TRANSACTION_FAILED,
                'updated_at' => now(),
            ]);

            // If failed, return the amount to user's balance
            if ($transferData['status'] !== 'success') {
                $user = $transfer->user;
                $user->balance += $transfer->amount;
                $user->save();
            }

            DB::commit();

            return response()->json(['message' => 'Transfer processed'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer approval processing failed: ' . $e->getMessage());
            return response()->json(['message' => 'Processing failed'], 500);
        }
    }

    public function initiatePayment(Request $request) {
        /**
         * @var App\Models\user $user
         */
        $user = Auth::user();
        $cartItems = $request->cartItems ?? null;
        $model = null;
        $txRef = 'TX-' . uniqid();
        $totalPrice = 0;
        $tax = 0; 
         
        if(!$cartItems) {
            return response()->json([
                'message' => $this->langService->getLang('cart_empty')
            ], 404);
        }


        $validation = [
            'cartItems.*' => ['required', 'array'],
            'cartItems.*.type' => ['required', Rule::in(ORDER_TYPES)],
            'cartItems.*.slug' => ['required', 'string'],
        ];

        $validator = Validator::make($request->all(), $validation, $this->langService->getLang('transactions'));
        
        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
                foreach ($cartItems as $item) {
                    if (!isset($item['type'], $item['slug'])) {
                        throw new \InvalidArgumentException($this->langService->getLang('order_not_found'));
                    }
                    
                    switch ($item['type']) {
                        case COURSE:
                            $model =  new Course;
                            break;
                        case BOOK:
                            $model = new  Book();
                            break;
                        case LIVE_CLASS:
                            $model = new Plan();
                            break;
                    }
                    
                    $order = $model->query()
                        ->where('slug', $item['slug'])
                        ->first();

                    if (!$order) {
                        return response()->json([
                            'message' => $this->langService->getLang('order_not_found')
                        ], 404);
                    }

                    
                    $checkTransactionExist = $order->transactions()
                        ->where('customer_id', $user->id)
                        ->where('product_type', $item['type'])
                        ->first();

                    if($checkTransactionExist) {
                        if ($checkTransactionExist->status === TRANSACTION_SUCCESS) {
                            return response()->json([
                                'message' => $this->langService->getLang('aleready exist')
                            ], 404);
                        }
 
                        $checkTransactionExist->update([ 
                            'tx_ref' => $txRef, 
                            'enrolled_at' => Carbon::now()->format('Y-m-d H:i:s')
                        ]);

                        $this->createDeposit($checkTransactionExist);
                    }else {
                        $transaction = $order->transactions()->create([
                            'slug' => Str::uuid(),
                            'user_id' => $order->user_id,
                            'tx_ref' => $txRef,
                            'amount' => $order->price,
                            'customer_id' => $user->id,
                            'status' => TRANSACTION_PENDING,
                            'product_type' => $item['type'],
                            'live_price_type' => $item['live_price_type'] ?? 'notLive',
                            'enrolled_at' => Carbon::now()->format('Y-m-d H:i:s')
                        ]);

                        $this->createDeposit($transaction);
                    }
                $totalPrice += $order->price;
                        
                } 
                
                $data = [
                    'amount' => $totalPrice,
                    'email' => $user->email,
                    'currency' => "ETB",
                    'first_name' => $user->first_name,
                    'last_name' => $user->first_name,
                    'tx_ref' => $txRef,
                    "return_url" => "http://127.0.0.1:8000/#/invoice-page/$txRef",
                    "customization" => [
                        "title" => $this->langService->getLang('unique_english_payment_transaction'),
                        "description" => $this->langService->getLang('my_payment_description'). 29392,
                    ]
                ];
        
                $response = $this->chapaService->initializePayment($data);    

               if (!isset($response['status'])) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Payment gateway error'
                    ], 500);
                }

               $this->studentActivities( $user->first_name . ' ' . $user->last_name . ' enrolled at'.Carbon::now()->format('Y-m-d H:i:s') . ' with transaction ID: ' . $txRef);
                
                $user->notify(new TransactionSuccessfulNotification($txRef, $totalPrice));
                $admins = User::whereHas('systemAdmin')->get();
                    foreach ($admins as $admin) {
                        $admin->notify(new TransactionSuccessfulNotification($txRef, $totalPrice));
                    }
        
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
        
        return response()->json([
            'message' => $this->langService->getLang('payment_initiated'),
            'checkout_url' => $response['data']['checkout_url'],
        ]);
    }

    public function transactions(Request $request) {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$user) return;

        /**
         * @var \Illuminate\Pagination\LengthAwarePaginator $transactions
         */

        $transactions = null;

        if ($user->has('systemAdmin')) {
            $transactions = Transaction::query()
                ->get();
        }

        if($user->has('instructor')){
            $transactions = Transaction::query()
                ->where('user_id', $user->id)
                ->get();
        }       
                        
        $courseSell = $transactions->where('product_type', COURSE)
                ->sum('amount');

        $bookSell = $transactions->where('product_type', BOOK)
            ->sum('amount');

        $liveSell = $transactions->where('product_type', LIVE)
            ->sum('amount');

        $totalSell = $transactions
            ->sum('amount');

        $transactionSummary = $transactions->groupBy(function ($transaction) {
            return $transaction->created_at->format('Y-m-d');
        })->map(function ($transactionsByDate) {
            return $transactionsByDate->sum('amount');
        })->all();

        $transactionToday = $transactions->where('created_at', '>=', Carbon::today())
            ->sum('amount');

        $transactionThisMonth = $transactions->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->sum('amount');
 
        if($request->summryLength === 'true') {

            $transactionSummary = $transactions->groupBy(function ($transaction) {
                return $transaction->created_at->format('Y-m');
            })->map(function ($transactionsByMonth) {
                return $transactionsByMonth->sum('amount');
            })->all(); 
        }

        $pagination = $transactions->toArray();
        unset($pagination['data']);

        return response()->json([
            'data' => TransactionHistoryResource::collection($transactions),
            'pagination' => $pagination,
            'courseSell' => $courseSell,
            'bookSell' => $bookSell,
            'liveSell' => $liveSell,
            'totalSell' => $totalSell,
            'transactionSummary' => $transactionSummary,
            'transactionToday' => $transactionToday,
            'transactionThisMonth' => $transactionThisMonth,
        ]);
    }

    public function transactionInvoce(String $txRef) {

        /**
         * @var App\Models\user $user
         */
        $validation = [
            'tx_ref' => ['required', 'string'],
        ];
        $validator = Validator::make(['tx_ref' => $txRef], $validation, $this->langService->getLang('transactions'));
        
        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        } 

        $user = Auth::user();
        $cartItems = [];
        $tax = 0;
        $date = null;
        $name = null;

        $transactionStatus = $this->chapaService->verifyPayment($txRef);

        

        if ($transactionStatus['status'] !== 'success') {
            return response()->json([
                'message' => $this->langService->getLang('transaction_not_successful')
            ], 404);
        } 

        $transactions = Transaction::query()
            ->where('customer_id', $user->id)
            ->where('tx_ref', $txRef)
            ->get(); 
        
        if (!$transactions) {
            return response()->json([
                'message' => $this->langService->getLang('transaction_not_found')
            ], 404);
        }

        $totalPrice = $transactions->sum('amount');

        if($transactionStatus['data']['amount'] !== $totalPrice) {
            
            return response()->json([
                'message' => 'somthing went wrong',
            ], 404);
        }
    
        try{
            DB::beginTransaction();
                foreach ($transactions as $transaction) {
 
                    $transaction->update([
                        'status' => TRANSACTION_SUCCESS
                    ]); 

                    $transaction->transfer()->update([
                        'status' => TRANSACTION_SUCCESS, 
                    ]);
        
                    switch ($transaction->product_type) {
                        case COURSE:
                            $name = $transaction->course->course_name;
                            break;
                        case BOOK:
                            $name = $transaction->book->title;
                            break;
                        case LIVE:
                            $name = $transaction->live->title;
                            break;
                    }
        
                    $cartItems[] = [
                        'name' => $name,
                        'price' => $transaction->amount,
                        'quantity' => 1,
                    ];
                    $date = $transaction->created_at->format('M d, Y, H:i:s');
                } 

            DB::commit();

            $user->notify(new TransactionSuccessfulNotification($txRef, $totalPrice));
            $admins = User::whereHas('systemAdmin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new TransactionSuccessfulNotification($txRef, $totalPrice));
                }
                
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'invoiceNumber' => $txRef,
            'date' => $date,
            'customerName' => $user->full_name,
            'customerEmail' => $user->email,
            'customerPhone' => $user->phone,
            'items' => $cartItems,
            'subtotal' => $totalPrice,
            'tax' => $tax,
            'total' => $totalPrice,
        ]);
    }

    public function refundTransaction($txRef) {

        $user = User::query()
            ->where('id', Auth::id())
            ->has('systemAdmin')
            ->first();

        $transactions = Transaction::query()
            ->where('tx_ref', $txRef)
            ->get();

        if (!$transactions) {
            return response()->json([
                'message' => $this->langService->getLang('transaction_not_found')
            ], 404);
        }


        $response = $this->chapaService->refundPayment($txRef);

        if($response['status'] === 'success') {
            foreach ($transactions as $transaction) {
                $transaction->update([
                    'status' => TRANSACTION_REFUNDED
                ]);
            }
        }

            return response()->json([
            'message' => $response['message']
        ]);
    }

    public function getBankList() {
        $response = $this->chapaService->getBankList();

        return response()->json([
            'data' => $response
        ]);
    }

    public function transferToBank(Request $request) {
        dd($this->checkTransferStatus());
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        $txRef = 'Trf-' . uniqid();

        $validation = [
            'amount' => ['required', 'numeric', 'min:1'],
        ];

        $validator = Validator::make($request->all(), $validation, $this->langService->getLang('transfers'));

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];
            return response()->json(['message' => $message], 422);
        }

        $bankInfo = $user->bankInfos()->first();

        if (!$bankInfo) {
            return response()->json(['message' => 'Bank account not found'], 404);
        }

        // Check balance
        $balance = Transfer::query()
            ->where('user_id', $user->id)
            ->where('status', TRANSACTION_SUCCESS)
            ->sum('deposits')
            - Transfer::query()
            ->where('user_id', $user->id)
            ->where('status', TRANSACTION_SUCCESS)
            ->sum('withdrawals');

        if ($balance < $request->amount) {
            return response()->json(['message' => $this->langService->getLang('insufficient_balance')], 400);
        }

        $data = [
            'account_number' => $bankInfo->account_number,
            'bank_code' => $bankInfo->bank_code,
            'amount' => $request->amount,
            'currency' => 'ETB',
            'reference' => $txRef,
            'callback_url' => route('chapa.transfer.callback'), // Add this line
        ];
 
 
            DB::beginTransaction();

            // Create transfer record
            $transfer = $this->createWithdraw($request->amount); 
            $response = $this->chapaService->transfer($data); 

            if ($response['status'] !== 'success') {
                DB::rollBack();
                return response()->json([
                    'message' => $response['message']
                ], 400);
            } 

            DB::commit();

            return response()->json([
                'message' => $this->langService->getLang('transfer_initiated'),
                'data' => $response,
                'transfer' => $transfer,
            ]);
       
    }

    public function testTransferApproval(Request $request) {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        $txRef = 'Trf-' . uniqid();

        $validation = [
            'amount' => ['required', 'numeric', 'min:1'],
        ];

        $validator = Validator::make($request->all(), $validation, $this->langService->getLang('transfers'));

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];
            return response()->json(['message' => $message], 422);
        }

        $bankInfo = $user->bankInfos()->first();

        if (!$bankInfo) {
            return response()->json(['message' => 'Bank account not found'], 404);
        }

        // Check balance
        $balance = Transfer::query()
            ->where('user_id', $user->id)
            ->where('status', TRANSACTION_SUCCESS)
            ->orderBy('id', 'desc')
            ->first()->balance ?? 0;


        if ($balance < $request->amount) {
            return response()->json(['message' => $this->langService->getLang('insufficient_balance')], 400);
        }

        $data = [
            'account_number' => $bankInfo->account_number,
            'bank_code' => $bankInfo->bank_code,
            'amount' => $request->amount,
            'currency' => 'ETB',
            'reference' => $txRef,
            'callback_url' => route('chapa.transfer.callback'),
        ];

        $response = Http::withToken(config('services.chapa.secret_key'))
            ->timeout(60) // Increase timeout to 60s
            ->post('https://api.chapa.co/v1/transfers', $data);

        if ($response->successful()) {
            return response()->json([
                'message' => 'Transfer initiated',
                'data' => $response->json(),
            ]);
        } else {
            return response()->json([
                'message' => 'Transfer failed',
                'error' => $response->json(),
            ], 400);
        }
    }

    // In your TransactionController
    public function checkTransferStatus()
    {
        $response = Http::withToken(config('services.chapa.secret_key'))
            ->get("https://api.chapa.co/v1/transfer/events/CTzAUrTZ1yVVm2");

        dd($response);
        return $response->json();
    }
}