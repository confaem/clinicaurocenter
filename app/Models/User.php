<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Admin\Perfil;
use App\Models\Admin\Permiso;
use App\Models\Configuracion\Personal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'personal_id',
        'perfil_id',
        'estado',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'estado' => 'boolean',
        ];
    }

    /* ================================================================== *
     * Relaciones
     * ================================================================== */

    /** Datos personales del usuario. */
    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    /** Perfil principal (campo legado, se conserva por compatibilidad). */
    public function perfil(): BelongsTo
    {
        return $this->belongsTo(Perfil::class, 'perfil_id');
    }

    /** Perfiles asignados (M:M). Es la fuente de verdad del RBAC. */
    public function perfiles(): BelongsToMany
    {
        return $this->belongsToMany(Perfil::class, 'perfil_user', 'user_id', 'perfil_id')
            ->withTimestamps();
    }

    /* ================================================================== *
     * Autorización (RBAC dinámico)
     * Ver docs/analisis/analisis-arquitectura-flowstock.md §10
     * ================================================================== */

    /**
     * ¿El usuario pertenece a un perfil marcado como superadministrador?
     * (Reemplaza al antiguo bypass por id === 1 de FlowStock.)
     */
    public function esSuperadmin(): bool
    {
        return $this->perfiles->contains(fn (Perfil $perfil) => (bool) $perfil->es_superadmin);
    }

    /**
     * ¿Tiene el usuario la acción $codigoAccion sobre el menú indicado?
     *
     * @param  string|int|null  $menuIdOEnlace  id de menú o nombre de ruta (menu.enlace)
     */
    public function tienePermiso(string|int|null $menuIdOEnlace, string $codigoAccion = 'ver'): bool
    {
        if (! $this->estado || $menuIdOEnlace === null) {
            return false;
        }

        $perfilIds = $this->perfiles->pluck('id')->all();

        if ($perfilIds === []) {
            return false;
        }

        $query = DB::table('perfil_menu_permiso as pmp')
            ->join('permisos as p', 'pmp.permiso_id', '=', 'p.id')
            ->join('menu as m', 'pmp.menu_id', '=', 'm.id')
            ->whereIn('pmp.perfil_id', $perfilIds)
            ->where('p.codigo', $codigoAccion)
            ->where('p.estado', true);

        if (is_numeric($menuIdOEnlace)) {
            $query->where('m.id', (int) $menuIdOEnlace);
        } else {
            $query->where('m.enlace', $menuIdOEnlace);
        }

        return $query->exists();
    }

    /**
     * Acciones permitidas sobre un módulo (usado por los endpoints `data`
     * para mostrar u ocultar acciones por fila).
     *
     * @return array<string, bool> ['ver' => true, 'crear' => true, ...]
     */
    public function permisosPorMenu(string|int|null $enlace): array
    {
        if (! $this->estado || $enlace === null) {
            return [];
        }

        if ($this->esSuperadmin()) {
            return Permiso::where('estado', true)
                ->pluck('codigo')
                ->mapWithKeys(fn (string $codigo) => [$codigo => true])
                ->all();
        }

        $perfilIds = $this->perfiles->pluck('id')->all();

        if ($perfilIds === []) {
            return [];
        }

        return DB::table('perfil_menu_permiso as pmp')
            ->join('permisos as p', 'pmp.permiso_id', '=', 'p.id')
            ->join('menu as m', 'pmp.menu_id', '=', 'm.id')
            ->whereIn('pmp.perfil_id', $perfilIds)
            ->where('m.enlace', $enlace)
            ->where('p.estado', true)
            ->pluck('p.codigo')
            ->unique()
            ->mapWithKeys(fn (string $codigo) => [$codigo => true])
            ->all();
    }
}
