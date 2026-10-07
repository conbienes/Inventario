<?php

namespace App\Providers;
use App\Models\BonoRegalo\TarjetaBonoR;
use App\Policies\BonoRegalo\TarjetaBonoRPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       Paginator::useBootstrap();
        View::share('theme','lte');

        $this->autorizacionBonoRegalo();
    }

    private function autorizacionBonoRegalo(): void
    {
        Gate::policy(TarjetaBonoR::class, TarjetaBonoRPolicy::class);

        // Administrador del módulo Bono Regalo (permisos 1 o 2 en el módulo 3)
        Gate::define('bono-regalo.admin', function ($user) {
            static $cache = []; // una consulta por usuario y petición

            return $cache[$user->getAuthIdentifier()] ??= DB::table('empleado_modulo_permiso')
                ->where('id_empleado', $user->getAuthIdentifier())
                ->where('id_modulo', config('bono_regalo.modulo_id'))
                ->whereIn('id_permiso', config('bono_regalo.permisos_admin'))
                ->exists();
        });

        // Ver / imprimir una venta: quien la hizo o un administrador del módulo
        Gate::define('bono-regalo.ver-venta', function ($user, int $empleadoId) {
            return $empleadoId === (int) $user->getAuthIdentifier() || Gate::forUser($user)->allows('bono-regalo.admin');
        });
    }
}
