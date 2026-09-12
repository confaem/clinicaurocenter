<?php

namespace App\Models\Configuracion;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * País (maestro de ubigeo). Solo Perú tiene `es_peru = true`.
 */
class Pais extends Model
{
    use HasFactory;

    protected $table = 'paises';

    protected $fillable = [
        'codigo',
        'nombre',
        'es_peru',
        'estado',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'es_peru' => 'boolean',
        'estado'  => 'boolean',
    ];

    public function departamentos(): HasMany
    {
        return $this->hasMany(Departamento::class, 'pais_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', true);
    }
}
