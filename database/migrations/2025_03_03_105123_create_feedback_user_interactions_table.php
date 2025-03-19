<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('feedback_user_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('feed_back_id')->constrained('feed_backs')->onDelete('cascade');
            $table->string('favorite')->default('notSelected');
            $table->boolean('reported')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'feed_back_id']); // Prevent duplicate entries
        });
    }

    public function down() {
        Schema::dropIfExists('feedback_user_interactions');
    }
};
