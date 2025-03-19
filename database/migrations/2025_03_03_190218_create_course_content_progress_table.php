<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseContentProgressTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('course_content_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('course_content_id');
      
            $table->string('progress')->default('0');
            $table->timestamps();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('course_module_id');

            // Define the foreign key constraints.
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('course_module_id')->references('id')->on('course_modules')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('course_content_id')->references('id')->on('course_contents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('course_content_progress');
    }
}
