<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (! Schema::hasTable('cache')) {
            Schema::create('cache', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->longText('value')->nullable();
                $table->integer('expiration')->nullable();
            });
        }

        if (! Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->text('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
    }
};
