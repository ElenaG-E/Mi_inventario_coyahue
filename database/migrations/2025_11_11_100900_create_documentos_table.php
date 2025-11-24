<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            // PK
            $table->id('id');

            // FKs
            $table->unsignedBigInteger('equipo_id');
            $table->unsignedBigInteger('usuario_id');

            // Metadatos del archivo
            $table->string('nombre_archivo', 255);   // nombre original
            $table->string('ruta_s3', 500);          // ruta/URL en S3
            $table->string('clave_s3', 300);         // key interna en S3 (ej: carpeta/archivo.pdf)
            $table->string('tipo', 50);              // categoría del documento (ej: factura, garantía)
            $table->string('mime_type', 100);        // tipo MIME (ej: application/pdf)
            $table->bigInteger('tamaño_bytes');      // tamaño en bytes
            $table->dateTime('fecha_subida');        // cuándo se subió

            // Claves foráneas
            $table->foreign('equipo_id')->references('id')->on('equipos');
            $table->foreign('usuario_id')->references('id')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};