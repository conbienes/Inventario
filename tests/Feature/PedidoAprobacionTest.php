<?php

namespace Tests\Feature;

use App\Mail\ProdAprob;
use App\Models\Seguridad\Usuario;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Flujo de aprobación de pedidos de Inventario (HomeInventario/AprobarSolicitud, Cumplir, Vista*).
 * Base SQLite en memoria; solo se crean las tablas que usa el flujo.
 */
class PedidoAprobacionTest extends TestCase
{
    use DatabaseTransactions;

    private const CATEGORIA_PAPELERIA = 17;

    protected function setUp(): void
    {
        parent::setUp();

        $this->crearTablas();
        Mail::fake();
    }

    private function crearTablas(): void
    {
        $definiciones = [
            'areas' => function (Blueprint $t) {
                $t->id();
                $t->string('nombre')->nullable();
                $t->nullableTimestamps();
            },
            'empleados' => function (Blueprint $t) {
                $t->id();
                $t->string('cedula')->nullable();
                $t->string('nombre')->nullable();
                $t->string('apellidos')->nullable();
                $t->string('Correo')->nullable();
                $t->string('password')->nullable();
                $t->rememberToken();
                $t->integer('id_tipoempleado')->nullable();
                $t->integer('id_area')->nullable();
                $t->integer('id_div')->nullable();
                $t->nullableTimestamps();
            },
            'empleado_modulo_permiso' => function (Blueprint $t) {
                $t->unsignedBigInteger('id_empleado');
                $t->unsignedBigInteger('id_modulo');
                $t->unsignedBigInteger('id_permiso');
            },
            // Solo para que el menú del layout pueda consultar módulos (vacío en la prueba)
            'modulos' => function (Blueprint $t) {
                $t->id();
                $t->string('nombre')->nullable();
                $t->nullableTimestamps();
            },
            'empleado_modulo' => function (Blueprint $t) {
                $t->unsignedBigInteger('empleado_id');
                $t->unsignedBigInteger('modulo_id');
            },
            'permisos_especiales' => function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('id_empleado');
                $t->string('codigo');
                $t->nullableTimestamps();
            },
            'Producto' => function (Blueprint $t) {
                $t->id();
                $t->string('Nombre')->nullable();
                $t->string('Codigo')->nullable();
                $t->nullableTimestamps();
            },
            'Inventario' => function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('IdProducto');
                $t->unsignedBigInteger('IdDivisiones')->nullable();
                $t->unsignedBigInteger('IdCategoria')->nullable();
                $t->integer('Cantidades')->default(0);
                $t->nullableTimestamps();
            },
            'solicitudPedidos' => function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('IdSolicita')->nullable();
                $t->unsignedBigInteger('IdArea')->nullable();
                $t->unsignedBigInteger('IdAprueba')->nullable();
                $t->date('FechaTentativa')->nullable();
                $t->dateTime('FecAprobacion')->nullable();
                $t->text('Observaciones')->nullable();
                $t->integer('Estado')->default(1);
                $t->nullableTimestamps();
            },
            'pedidoinventario' => function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('IdInventario');
                $t->integer('CantidadS')->default(0);
                $t->integer('CantidadAp')->nullable();
                $t->string('Comentario')->nullable();
                $t->unsignedBigInteger('IdSolicitud');
                $t->nullableTimestamps();
            },
            'movimientos' => function (Blueprint $t) {
                $t->id();
                $t->integer('Cantidad')->default(0);
                $t->integer('IdTipoMovimiento')->nullable();
                $t->string('Descripcion')->nullable();
                $t->unsignedBigInteger('IdEmpleado')->nullable();
                $t->unsignedBigInteger('IdArea')->nullable();
                $t->unsignedBigInteger('IdSolicitante')->nullable();
                $t->unsignedBigInteger('IdInventario')->nullable();
                $t->nullableTimestamps();
            },
        ];

        foreach ($definiciones as $tabla => $fn) {
            if (!Schema::hasTable($tabla)) {
                Schema::create($tabla, $fn);
            }
        }
    }

    /** Crea un empleado de módulo 1 (Inventario) con los permisos especiales indicados. */
    private function empleado(array $permisosEspeciales = [], string $correo = 'persona@prueba.test'): Usuario
    {
        $id = DB::table('empleados')->insertGetId([
            'cedula' => (string) random_int(10000, 99999999),
            'nombre' => 'Empleado',
            'apellidos' => 'Prueba',
            'Correo' => $correo,
            'password' => Hash::make('Clave#2025'),
            'id_area' => 1,
            'id_div' => 1,
            'id_tipoempleado' => 2,
        ]);
        DB::table('empleado_modulo_permiso')->insert(['id_empleado' => $id, 'id_modulo' => 1, 'id_permiso' => 1]);

        foreach ($permisosEspeciales as $codigo) {
            DB::table('permisos_especiales')->insert(['id_empleado' => $id, 'codigo' => $codigo]);
        }

        return Usuario::findOrFail($id);
    }

    /**
     * Crea un pedido pendiente con una línea por producto.
     * $lineas: [ ['cantidad' => 5, 'stock' => 20, 'categoria' => 1], ... ]
     * Devuelve [idSolicitud, [idPedidoInventario...], [idInventario...]].
     */
    private function pedidoPendiente(array $lineas): array
    {
        $areaId = DB::table('areas')->insertGetId(['nombre' => 'Mantenimiento']);
        $solicitante = $this->empleado([], 'solicitante@prueba.test');

        $idSolicitud = DB::table('solicitudPedidos')->insertGetId([
            'IdSolicita' => $solicitante->id,
            'IdArea' => $areaId,
            'FechaTentativa' => now()->addDays(3)->toDateString(),
            'Estado' => 1,
        ]);

        $idsPedido = [];
        $idsInventario = [];
        foreach ($lineas as $linea) {
            $productoId = DB::table('Producto')->insertGetId(['Nombre' => 'Producto ' . random_int(1, 9999), 'Codigo' => 'P-' . random_int(1, 9999)]);
            $idInventario = DB::table('Inventario')->insertGetId([
                'IdProducto' => $productoId,
                'IdDivisiones' => 1,
                'IdCategoria' => $linea['categoria'],
                'Cantidades' => $linea['stock'],
            ]);
            $idsPedido[] = DB::table('pedidoinventario')->insertGetId([
                'IdInventario' => $idInventario,
                'CantidadS' => $linea['cantidad'],
                'IdSolicitud' => $idSolicitud,
            ]);
            $idsInventario[] = $idInventario;
        }

        return [$idSolicitud, $idsPedido, $idsInventario];
    }

    /** Payload que envía el formulario de Cumplir (una cantidad por línea). */
    private function payload(int $idSolicitud, array $idsPedido, array $idsInventario, array $cantidades): array
    {
        return [
            'IdSolicitud' => $idSolicitud,
            'IdPedido' => $idsPedido,
            'IdInventario' => $idsInventario,
            'Catidad' => $cantidades,
            'Comentario' => array_fill(0, count($idsPedido), 'entrega de prueba'),
        ];
    }

    private function stock(int $idInventario): int
    {
        return (int) DB::table('Inventario')->where('Id', $idInventario)->value('Cantidades');
    }

    public function test_aprobacion_valida_descuenta_stock_registra_movimiento_y_envia_correo(): void
    {
        $aprobador = $this->empleado(['APROBAR_SOLICITUDES']);
        [$idSolicitud, $idsPedido, $idsInv] = $this->pedidoPendiente([['cantidad' => 5, 'stock' => 20, 'categoria' => 1]]);

        $this->actingAs($aprobador)
            ->post('/AprobarSolicitud', $this->payload($idSolicitud, $idsPedido, $idsInv, [5]))
            ->assertRedirect('/Solicitudes')
            ->assertSessionHas('success')
            ->assertSessionMissing('warning');

        $this->assertSame(15, $this->stock($idsInv[0]), 'Se descuenta el stock aprobado');

        $this->assertSame(1, DB::table('movimientos')->count());
        $mov = DB::table('movimientos')->first();
        $this->assertSame(5, (int) $mov->Cantidad);
        $this->assertSame(1, (int) $mov->IdTipoMovimiento);
        $this->assertSame($aprobador->id, (int) $mov->IdEmpleado);
        $this->assertSame($idsInv[0], (int) $mov->IdInventario);

        $solicitud = DB::table('solicitudPedidos')->where('Id', $idSolicitud)->first();
        $this->assertSame(2, (int) $solicitud->Estado);
        $this->assertSame($aprobador->id, (int) $solicitud->IdAprueba);
        $this->assertNotNull($solicitud->FecAprobacion);

        $this->assertSame(5, (int) DB::table('pedidoinventario')->where('Id', $idsPedido[0])->value('CantidadAp'));

        Mail::assertSent(ProdAprob::class, fn (ProdAprob $m) => $m->hasTo('solicitante@prueba.test'));
    }

    public function test_sin_permiso_de_aprobacion_no_aprueba_nada(): void
    {
        $sinPermiso = $this->empleado([]);
        [$idSolicitud, $idsPedido, $idsInv] = $this->pedidoPendiente([['cantidad' => 5, 'stock' => 20, 'categoria' => self::CATEGORIA_PAPELERIA]]);

        $this->actingAs($sinPermiso)
            ->post('/AprobarSolicitud', $this->payload($idSolicitud, $idsPedido, $idsInv, [5]))
            ->assertRedirect('/Solicitudes')
            ->assertSessionHas('success1');

        $this->assertSame(20, $this->stock($idsInv[0]));
        $this->assertSame(0, DB::table('movimientos')->count());
        $this->assertSame(1, (int) DB::table('solicitudPedidos')->where('Id', $idSolicitud)->value('Estado'));
        Mail::assertNothingSent();
    }

    public function test_cantidad_mayor_al_stock_da_error_y_no_cambia_nada(): void
    {
        $aprobador = $this->empleado(['APROBAR_SOLICITUDES']);
        [$idSolicitud, $idsPedido, $idsInv] = $this->pedidoPendiente([
            ['cantidad' => 5, 'stock' => 3, 'categoria' => 1],
            ['cantidad' => 2, 'stock' => 10, 'categoria' => 1],
        ]);

        $respuesta = $this->actingAs($aprobador)
            ->from('/Cumplir')
            ->post('/AprobarSolicitud', $this->payload($idSolicitud, $idsPedido, $idsInv, [5, 2]));
        $respuesta->assertSessionHasErrors('general');
        $this->assertStringContainsString('Existencia insuficiente', $respuesta->baseResponse->getSession()->get('errors')->first('general'));

        $this->assertSame(3, $this->stock($idsInv[0]), 'Stock sin cambios');
        $this->assertSame(10, $this->stock($idsInv[1]), 'Ni la línea válida se descuenta: todo o nada');
        $this->assertSame(0, DB::table('movimientos')->count());
        $this->assertSame(1, (int) DB::table('solicitudPedidos')->where('Id', $idSolicitud)->value('Estado'));
        $this->assertNull(DB::table('pedidoinventario')->where('Id', $idsPedido[0])->value('CantidadAp'));
        Mail::assertNothingSent();
    }

    public function test_cantidad_aprobada_no_puede_superar_lo_solicitado(): void
    {
        $aprobador = $this->empleado(['APROBAR_SOLICITUDES']);
        [$idSolicitud, $idsPedido, $idsInv] = $this->pedidoPendiente([['cantidad' => 2, 'stock' => 50, 'categoria' => 1]]);

        $this->actingAs($aprobador)
            ->from('/Cumplir')
            ->post('/AprobarSolicitud', $this->payload($idSolicitud, $idsPedido, $idsInv, [3]))
            ->assertSessionHasErrors('general');

        $this->assertSame(50, $this->stock($idsInv[0]));
        $this->assertSame(1, (int) DB::table('solicitudPedidos')->where('Id', $idSolicitud)->value('Estado'));
    }

    public function test_cantidades_negativas_o_no_enteras_se_rechazan(): void
    {
        $aprobador = $this->empleado(['APROBAR_SOLICITUDES']);
        [$idSolicitud, $idsPedido, $idsInv] = $this->pedidoPendiente([['cantidad' => 5, 'stock' => 20, 'categoria' => 1]]);

        $this->actingAs($aprobador)
            ->from('/Cumplir')
            ->post('/AprobarSolicitud', $this->payload($idSolicitud, $idsPedido, $idsInv, [-1]))
            ->assertSessionHasErrors('Catidad.0');

        $this->actingAs($aprobador)
            ->from('/Cumplir')
            ->post('/AprobarSolicitud', $this->payload($idSolicitud, $idsPedido, $idsInv, ['abc']))
            ->assertSessionHasErrors('Catidad.0');

        $this->assertSame(20, $this->stock($idsInv[0]));
        $this->assertSame(1, (int) DB::table('solicitudPedidos')->where('Id', $idSolicitud)->value('Estado'));
    }

    public function test_pedido_ya_aprobado_no_vuelve_a_descontar_stock(): void
    {
        $aprobador = $this->empleado(['APROBAR_SOLICITUDES']);
        [$idSolicitud, $idsPedido, $idsInv] = $this->pedidoPendiente([['cantidad' => 5, 'stock' => 20, 'categoria' => 1]]);

        $this->actingAs($aprobador)->post('/AprobarSolicitud', $this->payload($idSolicitud, $idsPedido, $idsInv, [5]));
        $this->actingAs($aprobador)
            ->from('/Cumplir')
            ->post('/AprobarSolicitud', $this->payload($idSolicitud, $idsPedido, $idsInv, [5]))
            ->assertSessionHasErrors('general');

        $this->assertSame(15, $this->stock($idsInv[0]), 'Solo se descuenta una vez');
        $this->assertSame(1, DB::table('movimientos')->count());
    }

    public function test_id_cifrado_invalido_responde_404(): void
    {
        $aprobador = $this->empleado(['APROBAR_SOLICITUDES']);

        $this->actingAs($aprobador)->get('/abc-no-cifrado/Cumplir')->assertNotFound();
        $this->actingAs($aprobador)->get('/abc-no-cifrado/VistaPendientes')->assertNotFound();
        $this->actingAs($aprobador)->get('/abc-no-cifrado/VistaAprobados')->assertNotFound();
    }

    public function test_papeleria_con_pedido_sin_papeleria_es_rechazado(): void
    {
        $papeleria = $this->empleado(['APROBAR_PAPELERIA']);
        [$idSolicitud, $idsPedido, $idsInv] = $this->pedidoPendiente([['cantidad' => 5, 'stock' => 20, 'categoria' => 1]]);

        $this->actingAs($papeleria)
            ->post('/AprobarSolicitud', $this->payload($idSolicitud, $idsPedido, $idsInv, [5]))
            ->assertRedirect('/Solicitudes')
            ->assertSessionHas('success1');

        $this->assertSame(20, $this->stock($idsInv[0]));
        $this->assertSame(1, (int) DB::table('solicitudPedidos')->where('Id', $idSolicitud)->value('Estado'));
    }

    public function test_papeleria_aprueba_pedido_con_papeleria(): void
    {
        $papeleria = $this->empleado(['APROBAR_PAPELERIA']);
        [$idSolicitud, $idsPedido, $idsInv] = $this->pedidoPendiente([
            ['cantidad' => 4, 'stock' => 10, 'categoria' => self::CATEGORIA_PAPELERIA],
            ['cantidad' => 1, 'stock' => 10, 'categoria' => 1],
        ]);

        $this->actingAs($papeleria)
            ->post('/AprobarSolicitud', $this->payload($idSolicitud, $idsPedido, $idsInv, [4, 1]))
            ->assertRedirect('/Solicitudes')
            ->assertSessionHas('success');

        $this->assertSame(6, $this->stock($idsInv[0]));
        $this->assertSame(9, $this->stock($idsInv[1]));
        $this->assertSame(2, DB::table('movimientos')->count(), 'Un movimiento por línea aprobada');
        $this->assertSame(2, (int) DB::table('solicitudPedidos')->where('Id', $idSolicitud)->value('Estado'));
    }

    public function test_aprobar_solicitudes_aprueba_cualquier_pedido(): void
    {
        $general = $this->empleado(['APROBAR_SOLICITUDES']);
        [$idSolicitud, $idsPedido, $idsInv] = $this->pedidoPendiente([['cantidad' => 3, 'stock' => 8, 'categoria' => 1]]);

        $this->actingAs($general)
            ->post('/AprobarSolicitud', $this->payload($idSolicitud, $idsPedido, $idsInv, [3]))
            ->assertRedirect('/Solicitudes')
            ->assertSessionHas('success');

        $this->assertSame(5, $this->stock($idsInv[0]));
        $this->assertSame(2, (int) DB::table('solicitudPedidos')->where('Id', $idSolicitud)->value('Estado'));
    }

    public function test_usuario_con_ambos_permisos_aprueba_pedido_sin_papeleria(): void
    {
        $ambos = $this->empleado(['APROBAR_PAPELERIA', 'APROBAR_SOLICITUDES']);
        [$idSolicitud, $idsPedido, $idsInv] = $this->pedidoPendiente([['cantidad' => 2, 'stock' => 6, 'categoria' => 1]]);

        $this->actingAs($ambos)
            ->post('/AprobarSolicitud', $this->payload($idSolicitud, $idsPedido, $idsInv, [2]))
            ->assertRedirect('/Solicitudes')
            ->assertSessionHas('success');

        $this->assertSame(4, $this->stock($idsInv[0]));
        $this->assertSame(2, (int) DB::table('solicitudPedidos')->where('Id', $idSolicitud)->value('Estado'));
    }

    public function test_fallo_del_correo_no_revierte_la_aprobacion_y_avisa(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP no disponible'));

        $aprobador = $this->empleado(['APROBAR_SOLICITUDES']);
        [$idSolicitud, $idsPedido, $idsInv] = $this->pedidoPendiente([['cantidad' => 5, 'stock' => 20, 'categoria' => 1]]);

        $this->actingAs($aprobador)
            ->post('/AprobarSolicitud', $this->payload($idSolicitud, $idsPedido, $idsInv, [5]))
            ->assertRedirect('/Solicitudes')
            ->assertSessionHas('warning');

        $this->assertSame(15, $this->stock($idsInv[0]), 'El pedido sigue aprobado y descontado');
        $this->assertSame(2, (int) DB::table('solicitudPedidos')->where('Id', $idSolicitud)->value('Estado'));
    }

    public function test_vistas_de_aprobacion_renderizan_con_datos_reales(): void
    {
        $aprobador = $this->empleado(['APROBAR_SOLICITUDES']);
        [$idPendiente, $idsPedido, $idsInv] = $this->pedidoPendiente([['cantidad' => 5, 'stock' => 20, 'categoria' => 1]]);
        [$idAprobada, $idsPedido2, $idsInv2] = $this->pedidoPendiente([['cantidad' => 1, 'stock' => 9, 'categoria' => 1]]);

        // Aprueba la segunda solicitud para tener también una aprobada
        $this->actingAs($aprobador)->post('/AprobarSolicitud', $this->payload($idAprobada, $idsPedido2, $idsInv2, [1]));

        $pend = Crypt::encrypt($idPendiente);
        $aprob = Crypt::encrypt($idAprobada);

        $this->actingAs($aprobador)->get('/Solicitudes')->assertOk()->assertSee('Solicitudes por aprobar')->assertSee('Revisar');
        $this->actingAs($aprobador)->get("/{$pend}/Cumplir")->assertOk()->assertSee('Aprobar pedido')->assertSee('name="Catidad[]"', false);
        $this->actingAs($aprobador)->get("/{$aprob}/Cumplir")->assertOk()->assertSee('ya fue aprobada')->assertDontSee('name="Catidad[]"', false);
        $this->actingAs($aprobador)->get("/{$pend}/VistaPendientes")->assertOk()->assertSee('Detalle de la solicitud');
        $this->actingAs($aprobador)->get("/{$aprob}/VistaAprobados")->assertOk()->assertSee('Detalle de la solicitud aprobada');
        $this->actingAs($aprobador)->get('/Pendientes')->assertOk()->assertSee('Mis solicitudes pendientes');
        $this->actingAs($aprobador)->get('/Aprobadas')->assertOk()->assertSee('Mis solicitudes aprobadas');
        $this->actingAs($aprobador)->get('/TotalAprobadas')->assertOk()->assertSee('Solicitudes aprobadas');
        $this->actingAs($aprobador)->get('/Filtro')->assertOk()->assertSee('Filtrar solicitudes aprobadas');
    }
}
