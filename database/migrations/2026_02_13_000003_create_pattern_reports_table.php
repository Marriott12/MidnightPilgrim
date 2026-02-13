<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pattern_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_profile_id')->constrained('user_profiles')->onDelete('cascade');
            $table->string('pattern_type'); // abstraction_drift, repetitive_themes, rhythm_instability, etc.
            $table->text('description');
            $table->json('evidence')->nullable(); // Array of examples showing the pattern
            $table->text('correction_strategy');
            $table->text('specific_exercise');
            $table->boolean('acknowledged')->default(false);
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_profile_id', 'pattern_type']);
            $table->index(['user_profile_id', 'acknowledged']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pattern_reports');
    }
};
