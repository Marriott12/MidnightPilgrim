<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('fingerprint')->unique(); // Soft fingerprint (IP hash + user agent hash)
            
            // Emotional baseline metrics
            $table->float('emotional_baseline')->default(0.5); // 0-1 scale
            $table->float('volatility_score')->default(0.0); // 0-1 scale
            $table->integer('absolutist_language_frequency')->default(0);
            $table->float('self_criticism_index')->default(0.0); // 0-1 scale
            $table->json('recurring_topics')->nullable(); // Array of topics
            $table->json('time_of_day_emotional_drift')->nullable(); // Hour -> emotion mapping
            $table->float('session_depth_score')->default(0.0); // 0-1 scale
            
            // Personalization
            $table->string('preferred_mode')->default('quiet'); // quiet or company
            $table->integer('total_sessions')->default(0);
            $table->integer('sessions_since_reflection')->default(0);
            $table->timestamp('last_session_at')->nullable();
            
            $table->timestamps();
            
            $table->index('fingerprint');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_profiles');
    }
};
