<?php

namespace Tests\Feature;

use App\Models\Seguridad\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Catálogos de Inventario (Categoría y Almacén): alta por POST.
 * Base SQLite en memoria; solo se crean las tablas necesarias.
 * El middleware de autenticación/permisos se omite: aquí se prueba
 * el redirect tras guardar y la validación, no el control de acceso.
 */
class CatalogosTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->crearTablas();
        $this->withoutMiddleware();
    }

    private function crearTablas(): void
    {
        $definiciones = [
            'empleados' => function (Blueprint $t) {
                $t->id();
                $t->string('cedula')->nullable();
                $t->string('nombre')->nullable();
                $t->string('apellidos')->nullable();
                $t->string('Correo')->nullable();
                $t->string('password')->nullable();
                $t->rememberToken();
                $t->integer('id_tipoempleado')->nullable();
                $t->nullableTimestamps();
            },
            'categoria_inventario' => function (Blueprint $t) {
                $t->id();
                $t->string('Nombre', 100)->nullable();
                $t->integer('Estado')->nullable();
                $t->unsignedBigInteger('IdEmpleado')->nullable();
                $t->nullableTimestamps();
            },
            'Almacen' => function (Blueprint $t) {
                $t->id();
                $t->string('Nombre', 100)->nullable();
                $t->integer('Estado')->nullable();
                $t->unsignedBigInteger('IdEmpleado')->nullable();
                $t->nullableTimestamps();
            },
        ];

        foreach ($definiciones as $tabla => $definir) {
            if (!Schema::hasTable($tabla)) {
                Schema::create($tabla, $definir);
            }
        }
    }

    private function usuario(): Usuario
    {
        $usuario = Usuario::create(['cedula' => '1', 'nombre' => 'Prueba', 'apellidos' => 'Test']);
        DB::table('empleados')->where('id', $usuario->id)->update(['id_tipoempleado' => 1]);

        return $usuario->fresh();
    }

    public function test_guardar_categoria_redirige_al_listado(): void
    {
        $this->actingAs($this->usuario());

        $respuesta = $this->post('/Categoria/Crear', ['Nombre' => 'Lacteos', 'Estado' => 1]);

        $respuesta->assertStatus(302);
        $respuesta->assertRedirect(route('Categoria'));
        $respuesta->assertSessionHas('success');
        $this->assertDatabaseHas('categoria_inventario', ['Nombre' => 'LACTEOS']);
    }

    public function test_guardar_categoria_sin_nombre_da_error_de_validacion(): void
    {
        $this->actingAs($this->usuario());

        $respuesta = $this->post('/Categoria/Crear', ['Nombre' => '   ', 'Estado' => 1]);

        $respuesta->assertSessionHasErrors(['Nombre']);
        $this->assertDatabaseCount('categoria_inventario', 0);
    }

    public function test_guardar_categoria_duplicada_da_error_de_validacion(): void
    {
        $this->actingAs($this->usuario());
        DB::table('categoria_inventario')->insert(['Nombre' => 'BEBIDAS', 'Estado' => 1]);

        $respuesta = $this->post('/Categoria/Crear', ['Nombre' => 'bebidas', 'Estado' => 1]);

        $respuesta->assertSessionHasErrors(['Nombre']);
        $this->assertDatabaseCount('categoria_inventario', 1);
    }

    public function test_guardar_almacen_redirige_al_listado_y_no_devuelve_200(): void
    {
        $this->actingAs($this->usuario());

        $respuesta = $this->post('/Almacen/Crear', ['Nombre' => 'Bodega Norte', 'Estado' => 1]);

        $respuesta->assertStatus(302);
        $respuesta->assertRedirect(route('Almacen'));
        $this->assertDatabaseHas('Almacen', ['Nombre' => 'BODEGA NORTE']);
    }
}
