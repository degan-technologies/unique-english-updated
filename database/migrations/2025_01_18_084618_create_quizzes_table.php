<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();

            $table->string('question_type');
            $table->text('question');
            $table->json('choice');
            $table->json('answer');
            $table->text('hint')->nullable();
            
            $table->unsignedTinyInteger('not_deleted')->storedAs("IF(`deleted_at` IS NULL, 1, NULL)");

            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('q_meta_data_id')->nullable()->constrained('q_meta_data')->cascadeOnUpdate()->restrictOnDelete();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizes');
    }
};
