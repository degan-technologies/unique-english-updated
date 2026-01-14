<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('course_contents', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();

            $table->string('title');
            $table->integer('sequence');
            $table->longText('description')->nullable();

            $table->integer('content_type')->nullable();
            $table->string('content_url')->unique()->nullable();
            $table->string('thumbnail_url')->unique()->nullable();
            $table->boolean('video_optimized')->nullable();

            $table->time('hour')->nullable();
            $table->string('status')->default(PUBLISHED);
            $table->boolean('isDownloadable')->default(false);
            $table->boolean('video_optimized')->nullable();

            $table->unsignedTinyInteger('download_status')->storedAs("IF(`isDownloadable` IS NULL, 1, NULL)");
            $table->unsignedTinyInteger('not_deleted')->storedAs("IF(`deleted_at` IS NULL, 1, NULL)");

            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('course_module_id')->constrained('course_modules')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_contents');
    }
};
