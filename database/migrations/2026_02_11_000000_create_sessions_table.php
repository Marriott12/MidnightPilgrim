<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('conversation_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            $table->string('mode')->default('quiet'); // quiet or company
            $table->string('status')->default('active'); // active or closed
            $table->timestamps();
            
            $table->index(['uuid', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('conversation_sessions');
    }
};
