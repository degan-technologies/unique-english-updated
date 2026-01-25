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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->integer('gender')->nullable();

            $table->string('email')->nullable()->unique(); // Made nullable since users can register with phone
            $table->string('password');

            $table->string('temp_password')->nullable();

            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('user_name')->nullable()->unique();
            $table->string('full_name')->storedAs("CONCAT(`first_name`, ' ', COALESCE(`middle_name`, ''), ' ', COALESCE(`last_name`, ''))");

            $table->string('phone')->nullable()->unique();
            $table->string('profile')->nullable()->unique();
            $table->string('bg_image')->nullable()->unique();

            $table->integer('role');
            $table->timestamp('user_banned_at')->nullable();

            $table->integer('progress')->default(0);

            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable()->unique();

            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable(); // Add phone verification timestamp
            $table->rememberToken();
            $table->timestamps();

            $table->softDeletes();
            $table->integer('otp')->nullable();

            $table->timestamp('otp_expires_at')->nullable();

            $table->integer('otp_attempts')->default(0);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
