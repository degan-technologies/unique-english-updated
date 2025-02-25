<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Reference to users table
            $table->text('message'); // SMS content
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending'); // Status of the message
            $table->timestamps(); // Created and updated timestamps
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
