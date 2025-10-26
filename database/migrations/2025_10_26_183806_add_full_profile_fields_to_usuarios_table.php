<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Nombres completos (basado en tu SQL)
            $table->string('segundo_nombre', 100)->nullable()->after('primer_nombre');
            $table->string('segundo_apellido', 100)->nullable()->after('primer_apellido');

            // Campos de Identificación (ya los tenías)
            $table->string('dpi', 20)->nullable()->unique()->after('segundo_apellido');
            $table->string('nit', 15)->nullable()->unique()->after('dpi');
             $table->string('foto_perfil_url')->nullable()->after('telefono');

            // --- Campos de Dirección y Geografía ---

            // Asumo que tienes una tabla 'paises' con id INT UNSIGNED
            $table->unsignedInteger('pais_id')->nullable()->after('foto_perfil_url');

            $table->unsignedInteger('departamento_id')->nullable()->after('pais_id');

            $table->unsignedInteger('municipio_id')->nullable()->after('departamento_id');

            $table->unsignedInteger('aldea_id')->nullable()->after('municipio_id');

            // Campo de texto para la dirección
            $table->string('direccion')->nullable()->after('aldea_id');

            // Añade la columna 'deleted_at' para Soft Deletes
            $table->softDeletes();

            // --- Definición de Llaves Foráneas ---

            // NOTA: Descomenta estas líneas si tienes las tablas correspondientes
            // $table->foreign('pais_id')->references('id')->on('paises')->nullOnDelete();
            $table->foreign('departamento_id')->references('id')->on('departamentos')->nullOnDelete();
            $table->foreign('municipio_id')->references('id')->on('municipios')->nullOnDelete();
            $table->foreign('aldea_id')->references('id')->on('aldeas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropForeign(['departamento_id']);
            $table->dropForeign(['municipio_id']);
            $table->dropForeign(['aldea_id']);
            // $table->dropForeign(['pais_id']);

            $table->dropColumn([
                'segundo_nombre',
                'segundo_apellido',
                'pais_id',
                'departamento_id',
                'municipio_id',
                'aldea_id',
                'direccion'
            ]);

            $table->dropSoftDeletes();
        });
    }
};
