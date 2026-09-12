<?php

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Nodo del menú jerárquico que alimenta el sidebar.
 * El campo `enlace` es la clave de autorización del RBAC (nombre de ruta Laravel).
 */
class Menu extends Model
{
    use HasFactory;

    protected $table = 'menu';

    protected $fillable = [
        'bloque',
        'nivel',
        'nombre',
        'enlace',
        'icono',
        'parent_id',
        'orden',
        'estado',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'estado' => 'boolean',
        'orden'  => 'integer',
    ];

    /* ------------------------------------------------------------------ *
     * Relaciones
     * ------------------------------------------------------------------ */

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('orden');
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

    public function scopeRaices(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }
}
