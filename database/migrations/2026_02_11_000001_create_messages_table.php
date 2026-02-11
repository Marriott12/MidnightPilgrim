<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('session_id');
            $table->string('role'); // user or assistant
            $table->text('content');
            $table->timestamp('created_at');
            
            $table->foreign('session_id')
                  ->references('id')
                  ->on('conversation_sessions')
                  ->onDelete('cascade');
                  
            $table->index(['session_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('messages');
    }
};
