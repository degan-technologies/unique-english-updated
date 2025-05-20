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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->text('data'); 
            $table->timestamps();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('notifiable_users', function (Blueprint $table) {
            $table->id(); 
            $table->timestamp('read_at')->nullable(); 
            $table->timestamps();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('notification_id')->constrained('notifications')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifiable_users');
        Schema::dropIfExists('notifications');
    }
};
