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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();

            $table->string('currency')->default('ETB');
            $table->string('reference')->nullable();
            $table->string('status')->default('pending');
            $table->double('deposits')->nullable();
            $table->double('withdrawals')->nullable();
            $table->double('balance')->nullable();
            $table->string('chapa_reference')->Unique()->nullable(); 

            $table->timestamps();

            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('transaction_id')->nullable()->unique()->constrained('transactions')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
