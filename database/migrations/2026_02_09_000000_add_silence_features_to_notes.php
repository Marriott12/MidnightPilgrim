<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            // Write-only mode: content never processed by AI
            $table->boolean('write_only')->default(false)->after('visibility');
            
            // No-archive: content is volatile, never re-surfaced
            $table->boolean('no_archive')->default(false)->after('write_only');
            
            // Delayed reflection: surface later without timestamps
            $table->boolean('reflect_later')->default(false)->after('no_archive');
            
            // Held line: one phrase to carry forward faintly
            $table->text('held_line')->nullable()->after('reflect_later');
        });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn(['write_only', 'no_archive', 'reflect_later', 'held_line']);
        });
    }
};
