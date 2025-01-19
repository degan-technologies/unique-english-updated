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
            $table->integer('squence');
            $table->longText('description')->nullable();

            $table->integer('content_type');
            $table->string('content_url')->unique();
            $table->string('temnail_url')->unique();

            $table->integer('duration');
            $table->string('status');
            $table->text('note')->nullable();
            $table->boolean('isDownloadable');

            $table->unsignedTinyInteger('download_status')->storedAs("IF(`isDownloadable` IS NULL, 1, NULL)");
            $table->unsignedTinyInteger('not_deleted')->storedAs("IF(`deleted_at` IS NULL, 1, NULL)");

            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnUpdate()->restrictOnDelete();
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
