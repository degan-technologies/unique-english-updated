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
        Schema::create('answers', function (Blueprint $table) {
            $table->id();

            // Store the answer text
            $table->text('answer');

            // Store a reference to the question (QASection)
            $table->foreignId('question_id')->constrained('q_a_sections')->cascadeOnUpdate()->restrictOnDelete();

            // Store a reference to the course
            $table->foreignId('course_id')->constrained('courses')->cascadeOnUpdate()->restrictOnDelete();

            // Store a reference to the user who answered
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();

            // Soft delete functionality
            $table->timestamps();
            $table->softDeletes();

            // Flag to mark if the answer is deleted
            $table->unsignedTinyInteger('not_deleted')->storedAs("IF(`deleted_at` IS NULL, 1, NULL)");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
