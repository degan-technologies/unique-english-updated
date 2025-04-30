<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('day');
            $table->string('schedule_time');
            $table->string('status')->default('scheduled');
            $table->timestamps();

            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('assign_id')->nullable()->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('schedules');
    }
};
