<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('notes')) {
            Schema::table('notes', function (Blueprint $table) {
                if (! Schema::hasColumn('notes', 'visibility')) {
                    $table->string('visibility')->default('private')->after('path');
                }
            });
        }

        if (Schema::hasTable('quotes')) {
            Schema::table('quotes', function (Blueprint $table) {
                if (! Schema::hasColumn('quotes', 'visibility')) {
                    $table->string('visibility')->default('private')->after('path');
                }
            });
        }

        if (Schema::hasTable('daily_thoughts')) {
            Schema::table('daily_thoughts', function (Blueprint $table) {
                if (! Schema::hasColumn('daily_thoughts', 'visibility')) {
                    $table->string('visibility')->default('private')->after('body');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('notes')) {
            Schema::table('notes', function (Blueprint $table) {
                if (Schema::hasColumn('notes', 'visibility')) {
                    $table->dropColumn('visibility');
                }
            });
        }

        if (Schema::hasTable('quotes')) {
            Schema::table('quotes', function (Blueprint $table) {
                if (Schema::hasColumn('quotes', 'visibility')) {
                    $table->dropColumn('visibility');
                }
            });
        }

        if (Schema::hasTable('daily_thoughts')) {
            Schema::table('daily_thoughts', function (Blueprint $table) {
                if (Schema::hasColumn('daily_thoughts', 'visibility')) {
                    $table->dropColumn('visibility');
                }
            });
        }
    }
};
