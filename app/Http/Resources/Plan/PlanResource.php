<?php
namespace App\Http\Resources\Plan;

use App\Models\Plan\Plan;
use App\Models\Transaction\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class PlanResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'price' => $this->price,
            'one_to_one_price' => $this->one_to_one_price,
            'group_price' => $this->group_price,
            'duration' => $this->duration . ' months',
            'isMyLive' => $this->checkEligibility($this->id) ? true : false,
            'planType' => $this->checkEligibility($this->id) ? $this->checkEligibility($this->id)->live_price_type : null,
            'created_at' => $this->created_at->format('l, M-d-Y'), 
        ];
    }

    public static function checkEligibility($planId) {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return false;
        }

        $myTransactions = Transaction::query()
            ->where('customer_id', $user->id)
            ->where('plan_id', $planId)
            ->where('product_type', LIVE_CLASS)
            ->where('status', TRANSACTION_SUCCESS)
            ->first();

        if ($myTransactions) {
            return  $myTransactions;
        }

        return false;
    }
}
