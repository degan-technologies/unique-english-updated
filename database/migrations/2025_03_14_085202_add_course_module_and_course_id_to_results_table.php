<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('results', function (Blueprint $table) {
            $table->unsignedBigInteger('course_module_id')->nullable()->after('q_meta_data_id');
            $table->unsignedBigInteger('course_id')->nullable()->after('course_module_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('results', function (Blueprint $table) {
            $table->dropColumn('course_module_id');
            $table->dropColumn('course_id');
        });
    }
};
