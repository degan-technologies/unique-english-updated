<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->uuid('slug')->unique();
            $table->string('question');
            $table->json('choices'); // Store choices as JSON
            $table->json('answer');  // Store correct answers as JSON
            $table->unsignedBigInteger('user_id'); // This column must exist for the foreign key to work
            $table->integer('score')->nullable(); // User's score
            $table->string('level')->nullable(); // English Level (A1, A2, B1, etc.)
            $table->timestamps();

            // Ensure the foreign key constraint is added correctly
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade'); // Cascade delete the tests when a user is deleted
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};
