<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('logos', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('file_path');
            $table->timestamps();
            $table->softDeletes();  
        });
    }

    public function down()
    {
        Schema::dropIfExists('logos');
    }
};
