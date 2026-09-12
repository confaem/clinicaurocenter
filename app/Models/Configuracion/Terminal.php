<?php

namespace App\Models\Configuracion;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Terminal o puesto de trabajo (caja, admisión, consultorio, farmacia).
 */
class Terminal extends Model
{
    use HasFactory;

    protected $table = 'terminal';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'estado',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

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
