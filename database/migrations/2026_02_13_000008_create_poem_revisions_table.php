<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poem_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poem_id')->constrained('poems')->onDelete('cascade');
            $table->integer('version_number');
            $table->text('content');
            $table->text('changes_made')->nullable();
            $table->string('revision_type')->default('draft'); // draft, revision, final
            $table->timestamps();
            
            $table->index(['poem_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poem_revisions');
    }
};
