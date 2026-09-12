<?php

namespace App\Services;

use App\Models\Admin\Menu;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Construye y mantiene el contexto RBAC del usuario en sesión:
 * perfiles, permisos consolidados y árbol de menús accesibles.
 *
 * Ver docs/analisis/analisis-arquitectura-flowstock.md §9 y §10.
 *
 * Nota de rendimiento (deuda D12 del análisis): recalcular el contexto en cada
 * request costaba ~4 consultas. Ahora se usa una "versión RBAC" en cache: si la
 * versión no cambió, la sesión no se vuelve a construir. Cada vez que se guardan
 * perfiles, permisos, usuarios, menús o personal se debe llamar a invalidar().
 */
class SessionDataService
{
    /** Clave de cache que identifica la versión vigente del RBAC. */
    public const VERSION_KEY = 'urocenter.rbac.version';

    /**
     * Carga en sesión los datos del usuario autenticado.
     */
    public function cargarDatosEnSesion(User $user, bool $forzar = false): void
    {
        try {
            $version = Cache::get(self::VERSION_KEY) ?? self::invalidar();

            // Si nada cambió desde la última carga, no se repiten las consultas
            if (! $forzar && session('usuario_rbac_version') === $version) {
                return;
            }

            $user->load(['personal', 'perfiles']);

            $esSuperadmin = $user->esSuperadmin();
            $permisos = $this->obtenerPermisosConsolidados($user);
            $nombresPerfiles = $user->perfiles->pluck('nombre')->all();

            session([
                'usuario_perfiles_ids'  => $user->perfiles->pluck('id')->all(),
                'usuario_perfil_nombre' => $nombresPerfiles !== [] ? implode(', ', $nombresPerfiles) : 'Sin Perfil',
                'usuario_permisos'      => $permisos,
                'usuario_es_superadmin' => $esSuperadmin,
                'usuario_rbac_version'  => $version,
            ]);

            if ($user->personal) {
                session([
                    'usuario_personal_id'        => $user->personal->id,
                    'usuario_personal_nombres'   => $user->personal->nombres,
                    'usuario_personal_apellidos' => $user->personal->apellidos,
                    'usuario_personal_ndoc'      => $user->personal->numero_documento,
                    'usuario_display_name'       => trim($user->personal->nombres . ' ' . $user->personal->apellidos),
                ]);
            } else {
                session([
                    'usuario_display_name' => $user->name,
                    'usuario_personal_id'  => null,
                ]);
            }

            session(['usuario_menu_tree' => $this->construirMenuAccesible($permisos, $esSuperadmin)]);
        } catch (Throwable $e) {
            Log::error('Error al cargar el contexto RBAC en sesión', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Invalida el contexto RBAC cacheado: la próxima petición lo reconstruye.
     */
    public static function invalidar(): string
    {
        $version = (string) Str::uuid();
        Cache::forever(self::VERSION_KEY, $version);

        return $version;
    }

    /**
     * Limpia el contexto RBAC guardado en la sesión actual.
     */
    public static function limpiarSesion(): void
    {
        session()->forget([
            'usuario_perfiles_ids',
            'usuario_perfil_nombre',
            'usuario_permisos',
            'usuario_es_superadmin',
            'usuario_menu_tree',
            'usuario_rbac_version',
            'usuario_personal_id',
            'usuario_personal_nombres',
            'usuario_personal_apellidos',
            'usuario_personal_ndoc',
            'usuario_display_name',
        ]);
    }

    /**
     * Permisos consolidados de todos los perfiles del usuario.
     *
     * @return array{int: array<string, string>}  [menu_id => ['ver' => '1', 'crear' => '1', ...]]
     */
    private function obtenerPermisosConsolidados(User $user): array
    {
        $perfilIds = $user->perfiles->pluck('id')->all();

        if ($perfilIds === []) {
            return [];
        }

        $filas = DB::table('perfil_menu_permiso as pmp')
            ->join('permisos as p', 'pmp.permiso_id', '=', 'p.id')
            ->whereIn('pmp.perfil_id', $perfilIds)
            ->where('p.estado', true)
            ->select('pmp.menu_id', 'p.codigo')
            ->get();

        $consolidado = [];

        foreach ($filas as $fila) {
            $consolidado[$fila->menu_id][$fila->codigo] = '1';
        }

        return $consolidado;
    }

    /**
     * Árbol de menús visibles para el usuario.
     *
     * Un nodo se incluye cuando el usuario tiene el permiso 'acceso' o cuando
     * contiene descendientes accesibles. El superadministrador recibe el árbol
     * completo de menús activos (no depende de filas en perfil_menu_permiso).
     *
     * @return array<int, array<string, mixed>>
     */
    private function construirMenuAccesible(array $permisos, bool $esSuperadmin = false): array
    {
        $idsConAcceso = [];

        if ($esSuperadmin) {
            // El superadministrador ve todos los menús activos
            $menus = Menu::where('estado', true)
                ->orderBy('nivel')
                ->orderBy('bloque')
                ->orderBy('orden')
                ->get()
                ->keyBy('id');

            return $this->armarRama($menus, null, $idsConAcceso, true);
        }

        foreach ($permisos as $menuId => $acciones) {
            if (isset($acciones['acceso'])) {
                $idsConAcceso[] = $menuId;
            }
        }

        if ($idsConAcceso === []) {
            return [];
        }

        $menus = Menu::where('estado', true)
            ->orderBy('nivel')
            ->orderBy('bloque')
            ->orderBy('orden')
            ->get()
            ->keyBy('id');

        return $this->armarRama($menus, null, $idsConAcceso, false);
    }

    /**
     * Construye recursivamente una rama del menú (soporta 3+ niveles).
     */
    private function armarRama($menus, ?int $parentId, array $idsConAcceso, bool $esSuperadmin = false): array
    {
        $rama = [];

        foreach ($menus->where('parent_id', $parentId) as $menu) {
            $hijos = $this->armarRama($menus, $menu->id, $idsConAcceso, $esSuperadmin);

            $tieneAcceso = $esSuperadmin || in_array($menu->id, $idsConAcceso, true);

            // Se incluye el nodo si tiene acceso propio o si contiene descendientes accesibles
            if (! $tieneAcceso && $hijos === []) {
                continue;
            }

            $rama[] = [
                'id'        => $menu->id,
                'nivel'     => $menu->nivel,
                'nombre'    => $menu->nombre,
                'icono'     => $menu->icono,
                'enlace'    => $menu->enlace,
                'bloque'    => $menu->bloque,
                'parent_id' => $menu->parent_id,
                'hijos'     => $hijos,
            ];
        }

        return $rama;
    }
}
