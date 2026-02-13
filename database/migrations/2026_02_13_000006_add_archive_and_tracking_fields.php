<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add archive and tracking fields to poems
        Schema::table('poems', function (Blueprint $table) {
            $table->string('archive_path')->nullable()->after('is_penalty_poem');
            $table->string('recording_file_path')->nullable()->after('archive_path');
            $table->string('public_release_url')->nullable()->after('recording_file_path');
            $table->text('revision_notes')->nullable()->after('public_release_url');
            $table->boolean('reflection_completed')->default(false)->after('revision_notes');
            $table->text('constraint_violations')->nullable()->after('reflection_completed');
        });

        // Add timezone and platform declaration to user profiles
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('timezone')->default('UTC')->after('preferred_mode');
            $table->string('declared_platform')->nullable()->after('timezone');
        });

        // Add timezone to discipline contracts
        Schema::table('discipline_contracts', function (Blueprint $table) {
            $table->string('user_timezone')->default('UTC')->after('last_submission_at');
        });
    }

    public function down(): void
    {
        Schema::table('poems', function (Blueprint $table) {
            $table->dropColumn([
                'archive_path',
                'recording_file_path',
                'public_release_url',
                'revision_notes',
                'reflection_completed',
                'constraint_violations',
            ]);
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['timezone', 'declared_platform']);
        });

        Schema::table('discipline_contracts', function (Blueprint $table) {
            $table->dropColumn('user_timezone');
        });
    }
};
