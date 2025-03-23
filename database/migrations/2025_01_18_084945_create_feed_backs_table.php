<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feed_backs', function (Blueprint $table) {
            $table->id();

            $table->integer('rate');
            $table->text('comment');

            // New columns for like/dislike and report functionality
            $table->unsignedInteger('likes')->default(0);
            $table->unsignedInteger('dislikes')->default(0);
            $table->unsignedInteger('reports')->default(0);
            $table->string('report_issue_type')->nullable();
            $table->text('report_issue_details')->nullable();

            $table->unsignedTinyInteger('not_deleted')->storedAs("IF(`deleted_at` IS NULL, 1, NULL)");

            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('instractor_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('courses')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('book_id')->nullable()-> constrained('books')->cascadeOnUpdate()->restrictOnDelete();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_backs');
    }
};
