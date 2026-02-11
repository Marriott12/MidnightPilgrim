<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('notes', function (Blueprint $table) {
            if (!Schema::hasColumn('notes', 'embedding')) {
                $table->json('embedding')->nullable()->after('content');
            }
        });
    }

    public function down()
    {
        Schema::table('notes', function (Blueprint $table) {
            if (Schema::hasColumn('notes', 'embedding')) {
                $table->dropColumn('embedding');
            }
        });
    }
};
