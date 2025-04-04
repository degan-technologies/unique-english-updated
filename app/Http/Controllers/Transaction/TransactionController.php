<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Transaction\Trait\TransferTrait;
use App\Http\Resources\Transaction\TransactionResource;
use App\Models\Book\Book;
use App\Models\Course\Course;
use App\Models\Live\Live;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\Transfer;
use App\Models\User;
use App\Services\ChapaService;
use App\Services\LangService;
use Carbon\Carbon;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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
    use LogsActivity;

    public function __construct(LangService $langService, ChapaService $chapaService) {
        $this->langService = $langService;
        $this->chapaService = $chapaService;
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
                        case LIVE:
                            $model = new Live();
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
                    }else {
                        $transaction = $order->transactions()->create([
                            'slug' => Str::uuid(),
                            'user_id' => $order->user_id,
                            'tx_ref' => $txRef,
                            'amount' => $order->price,
                            'customer_id' => $user->id,
                            'status' => TRANSACTION_PENDING,
                            'product_type' => $item['type'],
                            'enrolled_at' => Carbon::now()->format('Y-m-d H:i:s')
                        ]);
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

               if($response['status'] == 'failed') {
                DB::rollBack();
                return response()->json([
                    'message' => $response['message']
                ], 500);
               }
        
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }

        $this->logActivity('enroll', 'User enrolled in course', 'User enrolled in course ID: ' . $request->course_id, $request->course_id);
        
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

        $transactions = Transaction::query()
                ->where('user_id', $user->id)
                ->where('status', TRANSACTION_SUCCESS)
                ->get();
                        
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
            'data' => TransactionResource::collection($transactions),
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

    public function transactionInvoce($txRef) {

        $user = Auth::user();
        $cartItems = [];
        $tax = 0;
        $date = null;
        $name = null;

        $transactionStatus = $this->chapaService->verifyPayment($txRef);


        if ($transactionStatus['status'] !== 'success') {
            return response()->json([
                'message' => $this->langService->getLang('transaction_not_successful')
            ], 400);
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

        try{
            DB::beginTransaction();
                foreach ($transactions as $transaction) {
                    $transaction->update([
                        'status' => TRANSACTION_SUCCESS
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

                $cheDepostitHistory = Transfer::query()
                    ->where('reference', $txRef)
                    ->first();

                if( !$cheDepostitHistory) {
                    $this->transferHistory($transaction, DEPOSIT, $totalPrice );
                }
 


            DB::commit();
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
        $user = User::query()
            ->where('id', Auth::id())
            ->has('systemAdmin')
            ->first();

        $txRef = 'Trf-' . uniqid();

        $validation = [
            'amount' => ['required', 'numeric'],
        ];

        $validator = Validator::make($request->all(), $validation, $this->langService->getLang('transfers'));

        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];

            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }

        $bankInfo = $user->bankInfos()->first();
        $withdralAmount = $request->amount ;

        $data = (object) [
            'account_number' => $bankInfo->account_number,
            'bank_code' => $bankInfo->bank_code,
            'amount' => $request->amount,
            'currency' => 'ETB',
            'reference' => $txRef,
        ];

        $balance = $this->getBalance()->getData()->data; 

        if ($balance < $request->amount) {
            return response()->json([
                'message' => $this->langService->getLang('insufficient_balance')
            ], 400);
        }
   
        $response = $this->chapaService->transfer($data);

        $this->transferHistory($data, WITHDRAWAL);

        if($response['status'] === 'success') {
            $this->transferHistory($data, WITHDRAWAL);
        }

        return response()->json([
            'message' => $response['message']
        ]);

    }
}