<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_profile_id')->constrained('user_profiles')->onDelete('cascade');
            $table->text('content');
            $table->integer('line_count');
            $table->string('constraint_type')->nullable(); // concrete_imagery, no_metaphors, etc.
            $table->string('status')->default('draft'); // draft, submitted, revised, published
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('publish_platform')->nullable();
            $table->json('critique')->nullable(); // Critique notes from MP
            $table->json('self_assessment')->nullable(); // User's answers to the 4 questions
            $table->integer('week_number'); // Week number in contract
            $table->integer('revision_count')->default(0);
            $table->boolean('is_monthly_release')->default(false);
            $table->boolean('is_penalty_poem')->default(false); // If this is due to a missed deadline
            $table->timestamps();
            
            $table->index(['user_profile_id', 'week_number']);
            $table->index(['user_profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poems');
    }
};
