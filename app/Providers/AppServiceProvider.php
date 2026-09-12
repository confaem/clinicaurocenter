<?php

namespace App\Providers;

use App\Helpers\PermisoHelper;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

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
        // Migraciones organizadas por carpeta de dominio.
        // IMPORTANTE: Laravel no recorre subcarpetas, cada dominio debe registrarse aquí.
        // (en FlowStock se olvidó registrar `migrations/perfiles` y el migrate fallaba)
        $this->loadMigrationsFrom(database_path('migrations/admin'));
        $this->loadMigrationsFrom(database_path('migrations/configuracion'));

        // ---- Directiva Blade de permisos (RBAC dinámico) ----
        // Uso: @can_do('configuracion.empresa.index', 'crear') ... @endcan_do
        Blade::if('can_do', function (string $enlace, string $accion = 'ver') {
            return PermisoHelper::puede($enlace, $accion);
        });
    }
}
