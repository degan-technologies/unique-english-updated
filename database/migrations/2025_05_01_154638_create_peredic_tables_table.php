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
        Schema::create('peredic_tables', function (Blueprint $table) {
            $table->id();

            $table->unsignedTinyInteger('is_active')->storedAs("IF(`deleted_at` IS NULL, 1, NULL)");
            $table->softDeletes();
            $table->timestamps();

            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('live_room_id')->constrained('live_rooms')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peredic_tables');
    }
};
