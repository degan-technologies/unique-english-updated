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
        Schema::create('ins_attendances', function (Blueprint $table) {
            $table->id(); 

            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();

            $table->unsignedTinyInteger('is_active')->storedAs("IF(`deleted_at` IS NULL, 1, NULL)");
            $table->softDeletes();
            $table->timestamps();

            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('visiter_id')->nullable()->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ins_attendances');
    }
};
