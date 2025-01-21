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
        Schema::create('q_a_sections', function (Blueprint $table) {
            $table->id();

            $table->text('question');

            $table->unsignedTinyInteger('not_deleted')->storedAs("IF(`deleted_at` IS NULL, 1, NULL)");

            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('q_a_section_id')->constrained('q_a_sections')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('q_a_sections');
    }
};
