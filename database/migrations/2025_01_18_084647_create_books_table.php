<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();

            $table->string('title');
            $table->string('auther');
            $table->integer('page_number');
            $table->string('publish_date');
            $table->integer('eddition');
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('discount')->nullable();
            $table->longText('description');

            $table->string('language');
            $table->string('file_format');
            $table->string('cover_page_url')->unique();
            $table->string('file_url')->unique();
            $table->string('intro_vedio')->nullable()->unique();
            $table->boolean('video_optimized')->nullable();
            
            $table->json('tag');
            $table->boolean('isDownloadable');
            
            $table->unsignedTinyInteger('download_status')->storedAs("IF(`isDownloadable` IS NULL, 1, NULL)");
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
        Schema::dropIfExists('books');
    }
};
