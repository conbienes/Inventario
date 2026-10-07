<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Índices de integridad y rendimiento del módulo Bono Regalo.
 * Ejecutar SOLO esta migración:
 *   php artisan migrate --path=database/migrations/2026_10_07_000001_add_integrity_indexes_bono_regalo.php
 */
return new class extends Migration
{
    public function up(): void
    {
        // Una tarjeta solo puede venderse una vez (garantía en BD, además del lock en la venta)
        Schema::table('item_facturaBonoR', function (Blueprint $table) {
            $table->unique('tarjeta_id', 'item_facturabonor_tarjeta_id_unique');
        });

        // El número de factura no se puede repetir
        Schema::table('facturasBonoR', function (Blueprint $table) {
            $table->unique('numero_factura', 'facturasbonor_numero_factura_unique');
            $table->index(['estado_bc', 'fecha'], 'facturasbonor_estado_bc_fecha_index'); // pantalla Contabilidad
        });

        // CargaBC se consulta y agrupa por factura, fecha y cédula (informes e impresión)
        Schema::table('CargaBC', function (Blueprint $table) {
            $table->index('factura', 'cargabc_factura_index');
            $table->index('fecha', 'cargabc_fecha_index');
            $table->index('cedula', 'cargabc_cedula_index');
        });
    }

    public function down(): void
    {
        Schema::table('CargaBC', function (Blueprint $table) {
            $table->dropIndex('cargabc_factura_index');
            $table->dropIndex('cargabc_fecha_index');
            $table->dropIndex('cargabc_cedula_index');
        });

        Schema::table('facturasBonoR', function (Blueprint $table) {
            $table->dropUnique('facturasbonor_numero_factura_unique');
            $table->dropIndex('facturasbonor_estado_bc_fecha_index');
        });

        // Al quitar el UNIQUE se recrea el índice simple que necesita la FK tarjeta_id
        Schema::table('item_facturaBonoR', function (Blueprint $table) {
            $table->index('tarjeta_id', 'item_facturabonor_tarjeta_id_index_tmp');
            $table->dropUnique('item_facturabonor_tarjeta_id_unique');
        });
    }
};
