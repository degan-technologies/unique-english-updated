<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();

            $table->unsignedBigInteger('amount');
            $table->string('transaction_type')->nullable();
            $table->string('status')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('product_type');

            $table->string('tx_ref');
            $table->string('payment_url')->nullable();

            $table->time('enrolled_at');
            $table->timestamps();
            $table->softDeletes();


            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();

            $table->foreignId('course_id')->nullable()->constrained('courses')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('book_id')->nullable()->constrained('books')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('live_id')->nullable()->constrained('lives')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
