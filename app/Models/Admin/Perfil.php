<?php

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Perfil (rol) del sistema. Un usuario puede tener varios perfiles (M:M).
 * `es_superadmin` reemplaza el bypass por `id === 1` de FlowStock (deuda D8).
 */
class Perfil extends Model
{
    use HasFactory;

    protected $table = 'perfiles';

    protected $fillable = [
        'nombre',
        'descripcion',
        'es_superadmin',
        'estado',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'es_superadmin' => 'boolean',
        'estado'        => 'boolean',
    ];

    /* ------------------------------------------------------------------ *
     * Relaciones
     * ------------------------------------------------------------------ */

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'perfil_user', 'perfil_id', 'user_id')
            ->withTimestamps();
    }

    /** Acciones otorgadas al perfil (todas sus combinaciones). */
    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(Permiso::class, 'perfil_menu_permiso', 'perfil_id', 'permiso_id')
            ->withTimestamps();
    }

    /** Módulos (menús) sobre los que el perfil tiene alguna acción. */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'perfil_menu_permiso', 'perfil_id', 'menu_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /* ------------------------------------------------------------------ *
     * Scopes
     * ------------------------------------------------------------------ */

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', true);
    }
}
