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
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('video_optimized')->nullable();
        });
        Schema::table('course_contents', function (Blueprint $table) {
            $table->boolean('video_optimized')->nullable();
        });
        Schema::table('books', function (Blueprint $table) {
            $table->boolean('video_optimized')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('');
    }
};
