<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discipline_contract_id')->constrained('discipline_contracts')->onDelete('cascade');
            $table->foreignId('user_profile_id')->constrained('user_profiles')->onDelete('cascade');
            $table->integer('week_number');
            $table->boolean('on_time')->default(false);
            $table->boolean('revision_done')->default(false);
            $table->boolean('reflection_done')->default(false);
            $table->boolean('constraint_followed')->default(false);
            $table->boolean('penalty_triggered')->default(false);
            $table->string('status')->default('pending'); // pending, completed, missed, in_recovery
            $table->text('notes')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            
            $table->index(['discipline_contract_id', 'week_number']);
            $table->index(['user_profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_logs');
    }
};
