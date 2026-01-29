<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('check_ins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('mood');
            $table->tinyInteger('intensity');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('check_ins');
    }
};
