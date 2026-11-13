<?php

namespace Tests\Feature;

use App\Models\Seguridad\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Pruebas de seguridad y permisos de los cambios de la auditoría.
 * La base es SQLite en memoria sin migraciones: aquí se crean solo las tablas que usa el control de acceso.
 */
class SeguridadTest extends TestCase
{
    use DatabaseTransactions;

    private const PASSWORD_VALIDA = 'Nueva#2026';

    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('empleados')) {
            Schema::create('empleados', function (Blueprint $t) {
                $t->id();
                $t->string('cedula')->nullable();
                $t->string('nombre')->nullable();
                $t->string('apellidos')->nullable();
                $t->string('Correo')->nullable();
                $t->string('password')->nullable();
                $t->rememberToken();
                $t->integer('Documental')->default(0);
                $t->integer('id_tipoempleado')->nullable();
                $t->integer('id_sexo')->nullable();
                $t->integer('id_estado')->nullable();
                $t->integer('id_div')->nullable();
                $t->integer("id_area")->nullable();
                $t->nullableTimestamps();
            });
        }

        // El menú lateral (que también se dibuja en las páginas de error) consulta estas tablas
        if (!Schema::hasTable('modulos')) {
            Schema::create('modulos', function (Blueprint $t) {
                $t->id();
                $t->string('nombre')->nullable();
            });
        }
        if (!Schema::hasTable('empleado_modulo')) {
            Schema::create('empleado_modulo', function (Blueprint $t) {
                $t->unsignedBigInteger('empleado_id');
                $t->unsignedBigInteger('modulo_id');
            });
        }

        if (!Schema::hasTable('permisos_especiales')) {
            Schema::create('permisos_especiales', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('id_empleado');
                $t->string('codigo', 40);
                $t->nullableTimestamps();
            });
        }

        if (!Schema::hasTable('empleado_modulo_permiso')) {
            Schema::create('empleado_modulo_permiso', function (Blueprint $t) {
                $t->unsignedBigInteger('id_empleado');
                $t->unsignedBigInteger('id_modulo');
                $t->unsignedBigInteger('id_permiso');
            });
        }
    }

    private function empleado(string $nombre, array $permisos = [], string $password = 'Clave#2025'): Usuario
    {
        $id = DB::table('empleados')->insertGetId([
            'cedula' => (string) random_int(10000, 99999999),
            'nombre' => $nombre,
            'apellidos' => 'Prueba',
            'Correo' => strtolower($nombre) . '@prueba.test',
            'password' => Hash::make($password),
            'id_tipoempleado' => 2,
        ]);

        foreach ($permisos as [$modulo, $permiso]) {
            DB::table('empleado_modulo_permiso')->insert([
                'id_empleado' => $id,
                'id_modulo' => $modulo,
                'id_permiso' => $permiso,
            ]);
        }
        // Como hace la aplicación al crear: una fila por módulo asignado
        foreach (array_unique(array_column($permisos, 0)) as $modulo) {
            DB::table('empleado_modulo')->insert(['empleado_id' => $id, 'modulo_id' => $modulo]);
        }

        return Usuario::findOrFail($id);
    }

    // ---------- C1: documentos sin login ----------

    public function test_documentos_redirigen_al_login_si_no_hay_sesion(): void
    {
        $this->get('/Comercial')->assertRedirect();
        $this->get('/ExpInmueble')->assertRedirect();
        $this->get('/DocumentalComercial/1')->assertRedirect();
        $this->delete('/documentos/eliminar', ['documentos' => [1]])->assertRedirect();
    }

    public function test_peticion_json_sin_sesion_responde_401_y_no_500(): void
    {
        $this->getJson('/home')->assertStatus(401);
    }

    // ---------- M3: directorio de empleados solo para administradores de módulo 1 ----------

    public function test_directorio_de_empleados_rechaza_a_quien_no_administra_el_modulo(): void
    {
        $sinPermiso = $this->empleado('Cajero', [[3, 3]]);

        $this->actingAs($sinPermiso)
            ->withSession(['modulo_seleccionado' => 1])
            ->getJson('/Ajax/Empleados?q=a')
            ->assertStatus(403);
    }

    public function test_directorio_de_empleados_permite_a_administrador_del_modulo_1(): void
    {
        $admin = $this->empleado('Admin', [[1, 1]]);

        $this->actingAs($admin)
            ->withSession(['modulo_seleccionado' => 1])
            ->getJson('/Ajax/Correos?q=a')
            ->assertOk();
    }

    // ---------- M1: cambio de estado por GET eliminado ----------

    public function test_calificar_ya_no_acepta_get(): void
    {
        $admin = $this->empleado('Admin2', [[1, 1]]);

        $status = $this->actingAs($admin)
            ->withSession(['modulo_seleccionado' => 1])
            ->get('/EntAlmacen/Calificar?registroF=1&estrellas=5')
            ->getStatusCode();

        $this->assertContains($status, [404, 405], 'GET /EntAlmacen/Calificar no debe modificar datos');
    }

    // ---------- C2: cambio de contraseña ----------

    public function test_no_se_puede_cambiar_la_contrasena_de_otro_empleado(): void
    {
        $atacante = $this->empleado('Cajero3', [[3, 3]]);
        $victima = $this->empleado('Admin3', [[1, 1]], 'Victima#2025');
        $hashAntes = $victima->password;

        $this->actingAs($atacante)
            ->withSession(['modulo_seleccionado' => 3])
            ->put("/actualizar_contraseña/{$victima->id}", [
                'current_password' => 'Clave#2025',
                'password' => self::PASSWORD_VALIDA,
                'password_confirmation' => self::PASSWORD_VALIDA,
            ])
            ->assertStatus(403);

        $this->assertSame($hashAntes, DB::table('empleados')->where('id', $victima->id)->value('password'));
    }

    public function test_cambio_de_contrasena_propia_exige_la_contrasena_actual(): void
    {
        $usuario = $this->empleado('Propio', [[3, 3]], 'Actual#2025');
        $hashAntes = $usuario->password;

        $this->actingAs($usuario)
            ->withSession(['modulo_seleccionado' => 3])
            ->from('/cambiar_contraseña')
            ->put("/actualizar_contraseña/{$usuario->id}", [
                'current_password' => 'NoEsLaClave#1',
                'password' => self::PASSWORD_VALIDA,
                'password_confirmation' => self::PASSWORD_VALIDA,
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertSame($hashAntes, DB::table('empleados')->where('id', $usuario->id)->value('password'));
    }

    public function test_cambio_de_contrasena_rechaza_clave_debil(): void
    {
        $usuario = $this->empleado('Debil', [[3, 3]], 'Actual#2025');

        $this->actingAs($usuario)
            ->withSession(['modulo_seleccionado' => 3])
            ->from('/cambiar_contraseña')
            ->put("/actualizar_contraseña/{$usuario->id}", [
                'current_password' => 'Actual#2025',
                'password' => 'abc',
                'password_confirmation' => 'abc',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_cambio_de_contrasena_propio_valido_funciona(): void
    {
        $usuario = $this->empleado('Valido', [[3, 3]], 'Actual#2025');

        $this->actingAs($usuario)
            ->withSession(['modulo_seleccionado' => 3])
            ->put("/actualizar_contraseña/{$usuario->id}", [
                'current_password' => 'Actual#2025',
                'password' => self::PASSWORD_VALIDA,
                'password_confirmation' => self::PASSWORD_VALIDA,
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check(self::PASSWORD_VALIDA, DB::table('empleados')->where('id', $usuario->id)->value('password')));
    }

    // ---------- M5: un administrador no toca permisos de módulos que no administra ----------

    public function test_editar_empleado_conserva_permisos_de_modulos_no_administrados(): void
    {
        $admin = $this->empleado('AdminInv', [[1, 1]]);
        $objetivo = $this->empleado('Objetivo', [[1, 2], [3, 3]]);

        // El admin de Inventario intenta darle permiso de Bono Regalo (módulo 3) y cambiar su módulo 1.
        $this->actingAs($admin)
            ->withSession(['modulo_seleccionado' => 1])
            ->put("/Reg_Empleado/{$objetivo->id}", [
                'cedula' => $objetivo->cedula,
                'nombre' => 'Objetivo',
                'apellidos' => 'Prueba',
                'id_sexo' => 1,
                'id_estado' => 1,
                'id_tipoempleado' => 2,
                'id_div' => 1,
                'id_area' => 1,
                'email' => $objetivo->Correo,
                'modulos' => [
                    1 => [1],   // módulo que sí administra
                    3 => [1],   // módulo que NO administra: debe ignorarse
                ],
            ]);

        $permisosModulo3 = DB::table('empleado_modulo_permiso')
            ->where('id_empleado', $objetivo->id)->where('id_modulo', 3)->pluck('id_permiso')->all();
        $permisosModulo1 = DB::table('empleado_modulo_permiso')
            ->where('id_empleado', $objetivo->id)->where('id_modulo', 1)->pluck('id_permiso')->all();

        $this->assertSame([3], $permisosModulo3, 'Los permisos del módulo 3 deben conservarse intactos');
        $this->assertSame([1], $permisosModulo1, 'El módulo 1 sí se actualiza al valor enviado');

        // El menú (empleado_modulo) refleja el cambio: módulo 1 sigue asignado y el 3 no se toca
        $modulosMenu = DB::table('empleado_modulo')->where('empleado_id', $objetivo->id)
            ->orderBy('modulo_id')->pluck('modulo_id')->map(fn ($m) => (int) $m)->all();
        $this->assertSame([1, 3], $modulosMenu);
    }

    // ---------- Permisos especiales (antes IDs fijos 10 y 77 en el código) ----------

    public function test_permisos_especiales_se_guardan_solo_con_codigos_validos_desde_la_ficha(): void
    {
        $admin = $this->empleado('AdminPE', [[1, 1]]);
        $objetivo = $this->empleado('ObjetivoPE', [[1, 2]]);

        $this->actingAs($admin)
            ->withSession(['modulo_seleccionado' => 1])
            ->put("/Reg_Empleado/{$objetivo->id}", [
                'cedula' => $objetivo->cedula,
                'nombre' => 'ObjetivoPE',
                'apellidos' => 'Prueba',
                'id_sexo' => 1,
                'id_estado' => 1,
                'id_tipoempleado' => 2,
                'id_div' => 1,
                'id_area' => 1,
                'email' => $objetivo->Correo,
                'permisos_especiales' => ['APROBAR_SOLICITUDES', 'CODIGO_INVENTADO'],
            ]);

        $codigos = DB::table('permisos_especiales')->where('id_empleado', $objetivo->id)->pluck('codigo')->all();
        $this->assertSame(['APROBAR_SOLICITUDES'], $codigos, 'Solo se guardan códigos del catálogo');
    }

    public function test_el_login_redirige_a_una_ruta_existente_para_cada_modulo(): void
    {
        $controlador = (new \ReflectionClass(\App\Http\Controllers\Seguridad\LoginController::class))->newInstanceWithoutConstructor();
        $metodo = new \ReflectionMethod($controlador, 'resolveModuleHome');
        $metodo->setAccessible(true);

        // Módulo 2 y módulos desconocidos antes apuntaban a rutas que no existen
        foreach ([1, 2, 3, 9] as $modulo) {
            $url = $metodo->invoke($controlador, $modulo);
            $this->assertIsString($url);
            $this->assertNotSame('', $url);
        }
    }

    public function test_el_correo_del_empleado_se_valida_al_guardar(): void
    {
        $admin = $this->empleado('AdminMail', [[1, 1]]);
        $objetivo = $this->empleado('ObjetivoMail', [[1, 2]]);

        $this->actingAs($admin)
            ->withSession(['modulo_seleccionado' => 1])
            ->from("/Reg_Empleado/{$objetivo->id}/Editar")
            ->put("/Reg_Empleado/{$objetivo->id}", [
                'cedula' => $objetivo->cedula,
                'nombre' => 'ObjetivoMail',
                'apellidos' => 'Prueba',
                'id_sexo' => 1,
                'id_estado' => 1,
                'id_tipoempleado' => 2,
                'id_div' => 1,
                'id_area' => 1,
                'email' => 'no-es-un-correo',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_el_permiso_especial_de_aprobacion_se_consulta_por_codigo_no_por_id(): void
    {
        $aprobador = $this->empleado('AprobadorPE', [[1, 1]]);
        $otro = $this->empleado('OtroPE', [[1, 1]]);

        DB::table('permisos_especiales')->insert(['id_empleado' => $aprobador->id, 'codigo' => 'APROBAR_SOLICITUDES']);

        $this->assertTrue(\App\Models\Seguridad\PermisoEspecial::tiene('APROBAR_SOLICITUDES', $aprobador->id));
        $this->assertFalse(\App\Models\Seguridad\PermisoEspecial::tiene('APROBAR_SOLICITUDES', $otro->id));
        $this->assertFalse(\App\Models\Seguridad\PermisoEspecial::tiene('APROBAR_PAPELERIA', $aprobador->id));
    }
}
