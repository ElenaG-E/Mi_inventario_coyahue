<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'descripcion')) {
                $table->text('descripcion')->nullable()->after('nombre');
            }
        });
    }

    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'descripcion')) {
                $table->dropColumn('descripcion');
            }
        });
    }
};
