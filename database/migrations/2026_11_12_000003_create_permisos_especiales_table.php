<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Permisos especiales por empleado (reemplazan los IDs fijos en el código).
 * Se administran desde la ficha del empleado; no hace falta tocar código para cambiarlos.
 *
 * Ejecutar SOLO esta migración:
 *   php artisan migrate --path=database/migrations/2026_11_12_000003_create_permisos_especiales_table.php
 *
 * Carga inicial: reproduce lo que hacían los IDs fijos (10 = papelería, 77 = solicitudes en general).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permisos_especiales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_empleado');
            $table->string('codigo', 40);
            $table->timestamps();

            $table->unique(['id_empleado', 'codigo'], 'permisos_especiales_empleado_codigo_unique');
        });

        DB::table('permisos_especiales')->insert([
            ['id_empleado' => 10, 'codigo' => 'APROBAR_PAPELERIA', 'created_at' => now(), 'updated_at' => now()],
            ['id_empleado' => 77, 'codigo' => 'APROBAR_SOLICITUDES', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos_especiales');
    }
};
