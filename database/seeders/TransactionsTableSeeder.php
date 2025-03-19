<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TransactionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('transactions')->insert([
            [
                'slug' => Str::uuid(),
                'amount' => 199.99,
                'transaction_type' => 'purchase',
                'status' => 'completed',
                'payment_method' => 'credit_card',
                'product_type' => 'course',
                'tx_ref' => Str::random(10),
                'payment_url' => 'https://payment.example.com/tx1',
                'enrolled_at' => Carbon::now(),
                'user_id' => 1,
                'customer_id' => 1,
                'course_id' => 4,
                'book_id' => 3,
                'live_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'slug' => Str::uuid(),
                'amount' => 49.99,
                'transaction_type' => 'subscription',
                'status' => 'pending',
                'payment_method' => 'paypal',
                'product_type' => 'course',
                'tx_ref' => Str::random(10),
                'payment_url' => 'https://payment.example.com/tx2',
                'enrolled_at' => Carbon::now(),
                'user_id' => 1,
                'customer_id' => 2,
                'course_id' => 3,
                'book_id' => 1,
                'live_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'slug' => Str::uuid(),
                'amount' => 299.99,
                'transaction_type' => 'live_class',
                'status' => 'completed',
                'payment_method' => 'bank_transfer',
                'product_type' => 'book',
                'tx_ref' => Str::random(10),
                'payment_url' => 'https://payment.example.com/tx3',
                'enrolled_at' => Carbon::now(),
                'user_id' => 2,
                'customer_id' => 3,
                'course_id' => 2,
                'book_id' => 2,
                'live_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
