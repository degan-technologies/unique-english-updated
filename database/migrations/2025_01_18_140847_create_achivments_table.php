<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('achivments', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();

            $table->string('name');
            $table->longText('description');
            $table->longText('required_action');

            $table->unsignedInteger('required_value');
            $table->string('reward_type');
            $table->unsignedInteger('reward_value');

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
        Schema::dropIfExists('achivments');
    }
};
