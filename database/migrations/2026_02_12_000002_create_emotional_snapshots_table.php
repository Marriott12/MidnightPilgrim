<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('emotional_snapshots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_profile_id');
            $table->unsignedBigInteger('session_id');
            
            // Snapshot of emotional state at session end
            $table->float('intensity')->default(0.0);
            $table->float('tone')->default(0.5);
            $table->integer('absolutist_count')->default(0);
            $table->integer('self_criticism_count')->default(0);
            $table->json('topics')->nullable();
            $table->integer('hour_of_day')->nullable();
            
            $table->timestamp('created_at');
            
            $table->foreign('user_profile_id')
                  ->references('id')
                  ->on('user_profiles')
                  ->onDelete('cascade');
                  
            $table->foreign('session_id')
                  ->references('id')
                  ->on('conversation_sessions')
                  ->onDelete('cascade');
                  
            $table->index(['user_profile_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('emotional_snapshots');
    }
};
