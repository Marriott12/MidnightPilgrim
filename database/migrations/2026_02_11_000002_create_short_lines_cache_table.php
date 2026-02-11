<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('short_lines_cache', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('note_id')->nullable();
            $table->text('line');
            $table->integer('weight')->default(1);
            $table->timestamps();
            
            $table->foreign('note_id')
                  ->references('id')
                  ->on('notes')
                  ->onDelete('cascade');
                  
            $table->index('weight');
        });
    }

    public function down()
    {
        Schema::dropIfExists('short_lines_cache');
    }
};
