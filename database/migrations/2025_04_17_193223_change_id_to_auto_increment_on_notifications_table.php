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
        Schema::table('notifications', function (Blueprint $table) {
            // Drop the UUID id column
            $table->dropColumn('id');

            // Add the auto-incrementing integer id column
            $table->id();  // This will create an auto-incrementing primary key
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Rollback by dropping the auto-incrementing id and adding back UUID
            $table->dropColumn('id');
            $table->uuid('id')->primary();
        });
    }
};
