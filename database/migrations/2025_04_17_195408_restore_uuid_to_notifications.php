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
        // Drop the auto-incrementing 'id' column
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('id');  // Remove the auto-incrementing id column
        });

        // Add UUID 'id' column back
        Schema::table('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary()->first();  // Add UUID id column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback by removing the UUID id and adding the auto-incrementing id back
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->id();  // Add back the auto-incrementing primary key
        });
    }
};
