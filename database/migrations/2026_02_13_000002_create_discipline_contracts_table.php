<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discipline_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_profile_id')->constrained('user_profiles')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('active'); // active, completed, violated, abandoned
            $table->integer('total_weeks');
            $table->integer('poems_submitted')->default(0);
            $table->integer('poems_missed')->default(0);
            $table->integer('monthly_releases')->default(0);
            $table->integer('monthly_releases_missed')->default(0);
            $table->json('missed_weeks')->nullable(); // Array of week numbers missed
            $table->timestamp('last_submission_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discipline_contracts');
    }
};
