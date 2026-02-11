<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add performance indexes for conversation system
     */
    public function up(): void
    {
        // Messages table indexes
        Schema::table('messages', function (Blueprint $table) {
            $table->index(['session_id', 'created_at'], 'messages_session_created_idx');
            $table->index('role', 'messages_role_idx');
        });

        // Conversation sessions table indexes
        Schema::table('conversation_sessions', function (Blueprint $table) {
            $table->index(['uuid', 'status'], 'sessions_uuid_status_idx');
            $table->index('mode', 'sessions_mode_idx');
        });

        // Notes table indexes
        Schema::table('notes', function (Blueprint $table) {
            $table->index('visibility', 'notes_visibility_idx');
            $table->index('type', 'notes_type_idx');
            $table->index('created_at', 'notes_created_at_idx');
        });

        // Short lines cache table indexes
        Schema::table('short_lines_cache', function (Blueprint $table) {
            $table->index('weight', 'short_lines_weight_idx');
            $table->index('note_id', 'short_lines_note_id_idx');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_session_created_idx');
            $table->dropIndex('messages_role_idx');
        });

        Schema::table('conversation_sessions', function (Blueprint $table) {
            $table->dropIndex('sessions_uuid_status_idx');
            $table->dropIndex('sessions_mode_idx');
        });

        Schema::table('notes', function (Blueprint $table) {
            $table->dropIndex('notes_visibility_idx');
            $table->dropIndex('notes_type_idx');
            $table->dropIndex('notes_created_at_idx');
        });

        Schema::table('short_lines_cache', function (Blueprint $table) {
            $table->dropIndex('short_lines_weight_idx');
            $table->dropIndex('short_lines_note_id_idx');
        });
    }
};
