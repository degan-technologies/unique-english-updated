<?php

use App\Models\User;
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
        Schema::create('private_rooms', function (Blueprint $table)  { 
            $table->id();
            $table->string('class_name');
            $table->unsignedTinyInteger('is_active')->storedAs("IF(`deleted_at` IS NULL, 1, NULL)");
            $table->softDeletes();
            $table->timestamps();
     
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('private_rooms');
    }
};
