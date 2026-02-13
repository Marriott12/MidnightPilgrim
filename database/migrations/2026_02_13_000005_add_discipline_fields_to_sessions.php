<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversation_sessions', function (Blueprint $table) {
            // Track conversation quality indicators for discipline enforcement
            $table->integer('vagueness_count')->default(0);
            $table->integer('abstraction_count')->default(0);
            $table->integer('avoidance_detected_count')->default(0);
            $table->json('topics_avoided')->nullable();
            $table->boolean('grandiosity_detected')->default(false);
            $table->boolean('self_mythologizing_detected')->default(false);
            $table->string('escalation_tone')->default('baseline'); // baseline, sharp
        });
    }

    public function down(): void
    {
        Schema::table('conversation_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'vagueness_count',
                'abstraction_count',
                'avoidance_detected_count',
                'topics_avoided',
                'grandiosity_detected',
                'self_mythologizing_detected',
                'escalation_tone',
            ]);
        });
    }
};
