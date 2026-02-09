<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('id');
            $table->unsignedBigInteger('source_note_id')->nullable()->after('body');
            $table->string('confidence')->default('manual')->after('source_note_id');
            
            // Remove unused author column
            if (Schema::hasColumn('quotes', 'author')) {
                $table->dropColumn('author');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['slug', 'source_note_id', 'confidence']);
            $table->string('author')->nullable();
        });
    }
};
