<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('conversation_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_profile_id')->nullable()->after('uuid');
            $table->string('fingerprint')->nullable()->after('user_profile_id');
            
            // Session-level emotional metrics
            $table->float('session_intensity')->default(0.0)->after('status'); // 0-1 scale
            $table->integer('absolutist_count')->default(0)->after('session_intensity');
            $table->integer('self_criticism_count')->default(0)->after('absolutist_count');
            $table->json('detected_topics')->nullable()->after('self_criticism_count');
            $table->float('emotional_tone')->default(0.5)->after('detected_topics'); // 0-1 scale
            $table->integer('message_count')->default(0)->after('emotional_tone');
            $table->timestamp('last_message_at')->nullable()->after('message_count');
            
            $table->foreign('user_profile_id')
                  ->references('id')
                  ->on('user_profiles')
                  ->onDelete('set null');
                  
            $table->index(['fingerprint', 'status']);
        });
    }

    public function down()
    {
        Schema::table('conversation_sessions', function (Blueprint $table) {
            $table->dropForeign(['user_profile_id']);
            $table->dropColumn([
                'user_profile_id',
                'fingerprint',
                'session_intensity',
                'absolutist_count',
                'self_criticism_count',
                'detected_topics',
                'emotional_tone',
                'message_count',
                'last_message_at'
            ]);
        });
    }
};
