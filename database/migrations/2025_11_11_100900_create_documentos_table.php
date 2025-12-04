<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Corrección: Se reemplaza 'equipo_id' por $table->morphs('documentable')
     * para implementar una relación polimórfica (asociar Documentos a Equipos, Insumos, etc.).
     */
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            // PK
            $table->id('id');

            // FKs (Polimórficas)
            // Añade las columnas documentable_id (unsignedBigInteger) y documentable_type (string).
            $table->morphs('documentable'); 
            
            // La clave foránea equipo_id se elimina a favor de la relación polimórfica.
            $table->unsignedBigInteger('usuario_id')->nullable(); // Se recomienda hacerlo nullable si no siempre se requiere un usuario.

            // Metadatos del archivo
            $table->string('nombre_archivo', 255);   // nombre original
            $table->string('ruta_s3', 500);          // ruta/URL en S3
            $table->string('clave_s3', 300);         // key interna en S3 (ej: carpeta/archivo.pdf)
            $table->string('tipo', 50);              // categoría del documento (ej: factura, garantía)
            $table->string('mime_type', 100);        // tipo MIME (ej: application/pdf)
            $table->bigInteger('tamaño_bytes');      // tamaño en bytes
            $table->dateTime('fecha_subida');        // cuándo se subió

            // Columna presente en el fillable del modelo Documento
            $table->integer('tiempo_garantia_meses')->nullable();

            // Clave foránea de usuario
            $table->foreign('usuario_id')->references('id')->on('usuarios');
            
            // NOTA IMPORTANTE: Si esta migración ya fue ejecutada, NO uses el comando
            // 'migrate:fresh'. Crea una NUEVA migración para hacer los cambios.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
