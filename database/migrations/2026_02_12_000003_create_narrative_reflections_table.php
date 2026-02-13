<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('narrative_reflections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_profile_id');
            
            // Generated every 5 sessions
            $table->json('pattern_observations'); // 3 observations
            $table->text('identified_contradiction')->nullable();
            $table->text('philosophical_question')->nullable();
            
            $table->boolean('shown_to_user')->default(false);
            $table->timestamp('shown_at')->nullable();
            
            $table->timestamp('created_at');
            
            $table->foreign('user_profile_id')
                  ->references('id')
                  ->on('user_profiles')
                  ->onDelete('cascade');
                  
            $table->index(['user_profile_id', 'shown_to_user']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('narrative_reflections');
    }
};
