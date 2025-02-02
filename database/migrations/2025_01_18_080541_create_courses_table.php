<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();

            $table->string('course_name');
            $table->longText('overview')->nullable();
            $table->unsignedInteger('skill_level')->nullable();

            $table->unsignedBigInteger('price')->nullable();
            $table->unsignedBigInteger('discount')->nullable();
            $table->integer('credit_hour')->nullable();

            $table->string('thumbnail_url')->unique()->nullable();
            $table->string('language')->nullable();
            $table->json('tag')->default('{}');
            $table->unsignedTinyInteger('not_deleted')->storedAs("IF(`deleted_at` IS NULL, 1, NULL)");
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
