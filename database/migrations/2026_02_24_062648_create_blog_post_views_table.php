<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_post_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_id')->constrained()->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('session_id')->nullable();
            $table->timestamp('viewed_at');

            // Index for faster queries
            $table->index(['blog_post_id', 'ip_address', 'viewed_at']);
            $table->index(['blog_post_id', 'user_id', 'viewed_at']);
            $table->index(['blog_post_id', 'session_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_views');
    }
};
