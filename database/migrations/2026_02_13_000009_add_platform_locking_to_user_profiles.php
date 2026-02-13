<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->boolean('platform_locked')->default(false)->after('declared_platform');
            $table->timestamp('platform_declared_at')->nullable()->after('platform_locked');
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['platform_locked', 'platform_declared_at']);
        });
    }
};
