<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
        {
    Schema::table('usuarios', function (Blueprint $table) {
        $table->string('estado')->default('activo')->change();
    });
        }
};
