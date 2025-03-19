<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCourseAndModuleColumnsToCourseContentProgressTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('course_content_progress', function (Blueprint $table) {
            // Add the new columns; adjust the position with 'after' if needed.
            $table->unsignedBigInteger('course_id')->after('course_content_id');
            $table->unsignedBigInteger('course_module_id')->after('course_id');

            // Define the foreign key constraints.
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('course_module_id')->references('id')->on('course_modules')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('course_content_progress', function (Blueprint $table) {
            // Drop the foreign keys first.
            $table->dropForeign(['course_id']);
            $table->dropForeign(['course_module_id']);
            // Then drop the columns.
            $table->dropColumn(['course_id', 'course_module_id']);
        });
    }
}
