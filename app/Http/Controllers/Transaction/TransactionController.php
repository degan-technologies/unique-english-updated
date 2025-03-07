<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Book\Book;
use App\Models\Course\Course;
use App\Models\Live\Live;
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
    
    /* Initialize Rave payment process
     * @param \Illuminate\Http\Request $request
     * @return mixed
     * @throws \Exception
     * this method will initiate the payment process
     * and return the checkout url
     * to the user
     * register order to the transaction table
     * and calculate the total price
     */
    public function initiatePayment(Request $request) {
        /**
         * @var App\Models\user $user
         */
        $user = Auth::user();
        $cartItems = $request->cartItems ?? null;
        $model = null;
        $txRef = 'TX-' . uniqid();
        $totalPrice = 0;
         
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

                    $model->transactions()->create([
                        'slug' => Str::uuid(),
                        'user_id' => $order->user_id,
                        'tx_ref' => $txRef,
                        'amount' => $order->price,
                        'customer_id' => $user->id,
                        'product_type' => $item['type'],
                        'enrolled_at' => Carbon::now()->format('Y-m-d H:i:s')
                    ]);
                    
                    $totalPrice += $order->price;
                }
                
                $data = [
                    'amount' => $totalPrice,
                    'email' => $user->email,
                    'currency' => "ETB",
                    'first_name' => $user->first_name,
                    'last_name' => $user->first_name,
                    'tx_ref' => $txRef,
                    "customization" => [
                        "title" => $this->langService->getLang('unique_english_payment_transaction'),
                        "description" => $this->langService->getLang('my_payment_description'). 29392,
                    ]
                ];
        
                $response = $this->chapaService->initializePayment($data);    
        
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
            'checkout_url' => $response['data']['checkout_url']
        ]);

    }
}