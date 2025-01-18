<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('vertual_class_enrollments', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();

            $table->string('price_plan');
            $table->timestamp('enrolled_at');
            $table->timestamp('end_date');
            $table->integer('remaining_date');

            $table->unsignedTinyInteger('not_deleted')->storedAs("IF(`deleted_at` IS NULL, 1, NULL)");

            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('instractor_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vertual_class_enrollments');
    }
};
