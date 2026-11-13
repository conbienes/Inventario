<?php

namespace Tests\Feature;

use App\Models\BonoRegalo\FacturaBonoR;
use App\Services\BonoRegalo\FacturaBCService;
use App\Services\BusinessCentralService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

/**
 * Un reintento de factura no debe duplicar líneas de diario en Business Central.
 * BusinessCentralService se sustituye por un doble: no se llama a BC real.
 */
class BcIdempotenciaTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.bc.company_id' => 'EMPRESA-TEST']);

        if (!Schema::hasTable('clientesBonoR')) {
            Schema::create('clientesBonoR', function (Blueprint $t) {
                $t->id();
                $t->string('cedula')->nullable();
                $t->nullableTimestamps();
            });
        }
        if (!Schema::hasTable('tarjetasBonoR')) {
            Schema::create('tarjetasBonoR', function (Blueprint $t) {
                $t->id();
                $t->string('numero')->nullable();
                $t->decimal('valor', 15, 2)->default(0);
                $t->nullableTimestamps();
            });
        }
        if (!Schema::hasTable('facturasBonoR')) {
            Schema::create('facturasBonoR', function (Blueprint $t) {
                $t->id();
                $t->string('numero_factura')->nullable();
                $t->date('fecha')->nullable();
                $t->unsignedBigInteger('cliente_id')->nullable();
                $t->decimal('total', 15, 2)->default(0);
                $t->unsignedBigInteger('usuario_id')->nullable();
                $t->string('estado_bc')->default('PENDIENTE');
                $t->integer('bc_intentos')->default(0);
                $t->text('bc_ultimo_error')->nullable();
                $t->nullableTimestamps();
            });
        }
        if (!Schema::hasTable('item_facturaBonoR')) {
            Schema::create('item_facturaBonoR', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('factura_id');
                $t->unsignedBigInteger('tarjeta_id');
                $t->decimal('precio_unitario', 15, 2)->default(0);
                $t->nullableTimestamps();
            });
        }
        if (!Schema::hasTable('payment_methods')) {
            Schema::create('payment_methods', function (Blueprint $t) {
                $t->id();
                $t->string('name')->nullable();
                $t->string('tipo')->nullable();
            });
        }
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('invoice_id');
                $t->unsignedBigInteger('method_id')->nullable();
                $t->decimal('amount', 15, 2)->default(0);
                $t->nullableTimestamps();
            });
        }
        if (!Schema::hasTable('bc_lineas_enviadas')) {
            Schema::create('bc_lineas_enviadas', function (Blueprint $t) {
                $t->id();
                $t->bigInteger('factura_id');
                $t->string('clave', 40);
                $t->decimal('importe', 15, 2);
                $t->string('bc_system_id', 36)->nullable();
                $t->nullableTimestamps();
                $t->unique(['factura_id', 'clave']);
            });
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** Factura con una tarjeta de 100 y sin pagos. Devuelve [factura, id de la tarjeta]. */
    private function factura(): array
    {
        $clienteId = DB::table('clientesBonoR')->insertGetId(['cedula' => '123456']);
        $tarjetaId = DB::table('tarjetasBonoR')->insertGetId(['numero' => 'TBR-0001', 'valor' => 100]);
        $facturaId = DB::table('facturasBonoR')->insertGetId([
            'numero_factura' => 'BR-TEST-' . random_int(1000, 9999),
            'fecha' => now()->toDateString(),
            'cliente_id' => $clienteId,
            'total' => 100,
            'usuario_id' => 1,
            'estado_bc' => 'PENDIENTE',
            'bc_intentos' => 0,
        ]);
        DB::table('item_facturaBonoR')->insert([
            'factura_id' => $facturaId,
            'tarjeta_id' => $tarjetaId,
            'precio_unitario' => 100,
        ]);

        return [FacturaBonoR::findOrFail($facturaId), $tarjetaId];
    }

    public function test_reintento_reconoce_la_linea_ya_guardada_en_bc_y_no_la_duplica(): void
    {
        $bc = Mockery::mock(BusinessCentralService::class);
        $this->app->instance(BusinessCentralService::class, $bc);

        [$factura, $tarjetaId] = $this->factura();

        // Intento 1: la línea de detalle se pierde (timeout) y la de total entra bien.
        $bc->shouldReceive('createCustomJournalLine')->twice()->andReturn(
            ['ok' => false, 'status' => 0, 'error' => 'timeout', 'data' => null],
            ['ok' => true, 'status' => 201, 'data' => ['systemId' => 'TOTAL-1']],
        );

        $primero = app(FacturaBCService::class)->enviar($factura);
        $this->assertSame('ERROR', $primero['estado_bc']);

        // Intento 2: BC sí tenía la línea de detalle. Se reconoce y NO se envía de nuevo.
        // (Ninguna otra llamada a createCustomJournalLine: Mockery fallaría si la hubiera.)
        $bc->shouldReceive('findCustomJournalLine')->once()->andReturn(['systemId' => 'DET-1']);

        $segundo = app(FacturaBCService::class)->enviar(FacturaBonoR::findOrFail($factura->id));

        $this->assertSame('ENVIADO', $segundo['estado_bc']);

        $clavesRegistradas = DB::table('bc_lineas_enviadas')
            ->where('factura_id', $factura->id)
            ->pluck('clave')
            ->sort()
            ->values()
            ->all();
        $this->assertSame(['DET:' . $tarjetaId, 'TOTAL'], $clavesRegistradas);
    }

    public function test_si_no_se_puede_verificar_en_bc_no_se_reenvia_la_linea(): void
    {
        $bc = Mockery::mock(BusinessCentralService::class);
        $this->app->instance(BusinessCentralService::class, $bc);

        [$factura] = $this->factura();

        // Intento 1: detalle con timeout, total correcto. Son exactamente dos envíos.
        $bc->shouldReceive('createCustomJournalLine')->twice()->andReturn(
            ['ok' => false, 'status' => 0, 'error' => 'timeout', 'data' => null],
            ['ok' => true, 'status' => 201, 'data' => ['systemId' => 'TOTAL-1']],
        );
        app(FacturaBCService::class)->enviar($factura);

        // Intento 2: la consulta de verificación falla. No debe haber ningún envío nuevo.
        $bc->shouldReceive('findCustomJournalLine')->once()->andThrow(new \RuntimeException('BC no responde'));

        $segundo = app(FacturaBCService::class)->enviar(FacturaBonoR::findOrFail($factura->id));

        $this->assertSame('ERROR', $segundo['estado_bc']);
        $this->assertStringContainsString('no se reenvía', implode(' ', $segundo['errores']));
    }
}
