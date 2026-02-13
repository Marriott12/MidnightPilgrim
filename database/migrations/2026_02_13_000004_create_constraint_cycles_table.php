<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('constraint_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_profile_id')->constrained('user_profiles')->onDelete('cascade');
            $table->foreignId('discipline_contract_id')->constrained('discipline_contracts')->onDelete('cascade');
            $table->integer('week_number');
            $table->string('constraint_type'); // concrete_imagery, no_metaphors, sustained_metaphor, second_person
            $table->text('constraint_description');
            $table->boolean('completed')->default(false);
            $table->timestamps();
            
            $table->index(['discipline_contract_id', 'week_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('constraint_cycles');
    }
};
