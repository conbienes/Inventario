<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registro de cada línea de diario que BC aceptó por factura.
 * Permite reintentar una factura en ERROR sin duplicar las líneas que ya entraron.
 *
 * Ejecutar SOLO esta migración:
 *   php artisan migrate --path=database/migrations/2026_10_07_000002_create_bc_lineas_enviadas_table.php
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bc_lineas_enviadas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('factura_id'); // facturasBonoR.id es BIGINT con signo
            $table->string('clave', 40); // "DET:{tarjeta_id}" o "TOTAL"
            $table->decimal('importe', 15, 2);
            $table->string('bc_system_id', 36)->nullable();
            $table->timestamps();

            $table->unique(['factura_id', 'clave'], 'bc_lineas_enviadas_factura_clave_unique');
            $table->foreign('factura_id')->references('id')->on('facturasBonoR');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bc_lineas_enviadas');
    }
};
