<?php

namespace Tests\Feature;

use App\Models\Seguridad\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Flujo "nuevo pedido" de Inventario (SolicProduc/Guardar).
 * Base SQLite en memoria; solo se crean las tablas que usa el pedido.
 */
class PedidoInventarioTest extends TestCase
{
    use DatabaseTransactions;

    private int $divisionA = 1;
    private int $divisionB = 2;

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
            'Producto' => function (Blueprint $t) {
                $t->id();
                $t->string('Nombre')->nullable();
                $t->string('Codigo')->nullable();
                $t->nullableTimestamps();
            },
            'Inventario' => function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('IdProducto');
                $t->unsignedBigInteger('IdDivisiones');
                $t->integer('Cantidades')->default(0);
                $t->nullableTimestamps();
            },
            'solicitudPedidos' => function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('IdSolicita')->nullable();
                $t->unsignedBigInteger('IdArea')->nullable();
                $t->date('FechaTentativa')->nullable();
                $t->text('Observaciones')->nullable();
                $t->integer('Estado')->default(1);
                $t->nullableTimestamps();
            },
            'pedidoinventario' => function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('IdInventario');
                $t->integer('CantidadS')->default(0);
                $t->integer('CantidadAp')->nullable();
                $t->unsignedBigInteger('IdSolicitud');
                $t->nullableTimestamps();
            },
        ];

        foreach ($definiciones as $tabla => $fn) {
            if (!Schema::hasTable($tabla)) {
                Schema::create($tabla, $fn);
            }
        }
    }

    /** Usuario de la división A con permiso de Inventario; devuelve [usuario, id_area]. */
    private function solicitante(): Usuario
    {
        $areaId = DB::table('areas')->insertGetId(['nombre' => 'Mantenimiento']);
        $id = DB::table('empleados')->insertGetId([
            'cedula' => (string) random_int(10000, 99999999),
            'nombre' => 'Ana',
            'apellidos' => 'Pedido',
            'Correo' => 'ana.pedido@prueba.test',
            'password' => Hash::make('Clave#2025'),
            'id_area' => $areaId,
            'id_div' => $this->divisionA,
            'id_tipoempleado' => 2,
        ]);
        DB::table('empleado_modulo_permiso')->insert(['id_empleado' => $id, 'id_modulo' => 1, 'id_permiso' => 3]);

        return Usuario::findOrFail($id);
    }

    private function producto(int $division, string $nombre = 'Guantes'): int
    {
        $productoId = DB::table('Producto')->insertGetId(['Nombre' => $nombre, 'Codigo' => 'G-' . random_int(1, 999)]);

        return DB::table('Inventario')->insertGetId([
            'IdProducto' => $productoId,
            'IdDivisiones' => $division,
            'Cantidades' => 50,
        ]);
    }

    private function datos(array $sobrescribir = []): array
    {
        return array_merge([
            'Fecha' => now()->addDays(3)->toDateString(),
            'Observaciones' => 'Urgente para el turno',
            'IdInventario' => [],
            'Cantidad' => [],
        ], $sobrescribir);
    }

    public function test_pedido_valido_se_guarda_completo_y_con_el_solicitante_de_la_sesion(): void
    {
        $usuario = $this->solicitante();
        $p1 = $this->producto($this->divisionA, 'Guantes');
        $p2 = $this->producto($this->divisionA, 'Cascos');

        $this->actingAs($usuario)
            ->post('/SolicProduc/Actualizar', $this->datos([
                'IdInventario' => [$p1, $p2],
                'Cantidad' => [5, 2],
                // Intento de suplantar al solicitante: debe ignorarse
                'IdSolicitante' => 9999,
            ]))
            ->assertRedirect('/homeInventario')
            ->assertSessionHasNoErrors();

        $solicitud = DB::table('solicitudPedidos')->first();
        $this->assertNotNull($solicitud);
        $this->assertSame($usuario->id, (int) $solicitud->IdSolicita, 'El solicitante sale de la sesión');
        $this->assertSame(2, DB::table('pedidoinventario')->where('IdSolicitud', $solicitud->id)->count());
        $this->assertSame([5, 2], DB::table('pedidoinventario')->where('IdSolicitud', $solicitud->id)->orderBy('id')->pluck('CantidadS')->map(fn ($c) => (int) $c)->all());
    }

    public function test_no_se_puede_pedir_un_producto_de_otra_division(): void
    {
        $usuario = $this->solicitante();
        $ajeno = $this->producto($this->divisionB, 'Producto ajeno');

        $this->actingAs($usuario)
            ->from('/SolicProduc')
            ->post('/SolicProduc/Actualizar', $this->datos(['IdInventario' => [$ajeno], 'Cantidad' => [1]]))
            ->assertSessionHasErrors('general');

        $this->assertSame(0, DB::table('solicitudPedidos')->count(), 'No debe quedar cabecera sin líneas');
        $this->assertSame(0, DB::table('pedidoinventario')->count());
    }

    public function test_cantidades_invalidas_se_rechazan(): void
    {
        $usuario = $this->solicitante();
        $p = $this->producto($this->divisionA);

        $this->actingAs($usuario)
            ->from('/SolicProduc')
            ->post('/SolicProduc/Actualizar', $this->datos(['IdInventario' => [$p], 'Cantidad' => [0]]))
            ->assertSessionHasErrors('Cantidad.0');

        $this->actingAs($usuario)
            ->from('/SolicProduc')
            ->post('/SolicProduc/Actualizar', $this->datos(['IdInventario' => [$p], 'Cantidad' => ['abc']]))
            ->assertSessionHasErrors('Cantidad.0');

        $this->assertSame(0, DB::table('solicitudPedidos')->count());
    }

    public function test_no_se_permiten_productos_repetidos_ni_fecha_pasada(): void
    {
        $usuario = $this->solicitante();
        $p = $this->producto($this->divisionA);

        $this->actingAs($usuario)
            ->from('/SolicProduc')
            ->post('/SolicProduc/Actualizar', $this->datos(['IdInventario' => [$p, $p], 'Cantidad' => [1, 2]]))
            ->assertSessionHasErrors('IdInventario.0');

        $this->actingAs($usuario)
            ->from('/SolicProduc')
            ->post('/SolicProduc/Actualizar', $this->datos([
                'Fecha' => now()->subDays(2)->toDateString(),
                'IdInventario' => [$p],
                'Cantidad' => [1],
            ]))
            ->assertSessionHasErrors('Fecha');

        $this->assertSame(0, DB::table('solicitudPedidos')->count());
    }

    public function test_usuario_sin_area_no_puede_crear_pedidos(): void
    {
        $usuario = $this->solicitante();
        DB::table('empleados')->where('id', $usuario->id)->update(['id_area' => null]);
        $p = $this->producto($this->divisionA);

        $this->actingAs(Usuario::findOrFail($usuario->id))
            ->from('/SolicProduc')
            ->post('/SolicProduc/Actualizar', $this->datos(['IdInventario' => [$p], 'Cantidad' => [1]]))
            ->assertSessionHasErrors('general');

        $this->assertSame(0, DB::table('solicitudPedidos')->count());
    }
}
