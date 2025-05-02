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
         // Get instructor before schema creation
        $user = User::query()
            ->has('systemAdmin')
            ->first();
    
        if (!$user) {
            throw new \Exception('No system admin user found');
        }

        Schema::create('live_rooms', function (Blueprint $table) use ($user)  {
            $table->id();

            $table->string('class_name')->default('A');
            $table->unsignedTinyInteger('is_active')->storedAs("IF(`deleted_at` IS NULL, 1, NULL)");
            $table->softDeletes();
            $table->timestamps();
            
            $table->unique(['class_name', 'user_id']);

            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('instructor_id')->default($user->id)->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_rooms'); 
    }
};
