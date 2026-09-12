<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Autorización central del sistema (RBAC dinámico).
 *
 * Uso:
 *   PermisoHelper::puede('configuracion.empresa.index', 'crear')
 *
 * El $enlace es el nombre de la ruta registrado en la tabla `menu.enlace`.
 * Ver docs/analisis/analisis-arquitectura-flowstock.md §10.
 */
class PermisoHelper
{
    /**
     * ¿El usuario autenticado puede ejecutar $accion sobre el módulo $enlace?
     *
     * @param  string|int|null  $enlace  Nombre de ruta (o id de menú)
     * @param  string  $accion  ver | crear | editar | eliminar | activar | desactivar | aprobar | exportar | acceso
     */
    public static function puede(string|int|null $enlace, string $accion = 'ver'): bool
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return false;
        }

        if ($user->esSuperadmin()) {
            return true;
        }

        return $user->tienePermiso($enlace, $accion);
    }

    /**
     * ¿El usuario puede ver el módulo? (usado por el menú lateral)
     */
    public static function puedeAcceder(string|int|null $enlace): bool
    {
        return self::puede($enlace, 'acceso');
    }
}
