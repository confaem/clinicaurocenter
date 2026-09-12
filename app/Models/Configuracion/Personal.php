<?php

namespace App\Models\Configuracion;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Personal de la clínica. Base de médicos, recepción, caja, farmacia y almacén.
 */
class Personal extends Model
{
    use HasFactory;

    protected $table = 'personal';

    protected $fillable = [
        'tipo_documento',
        'numero_documento',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'telefono',
        'email_personal',
        'direccion',
        'ubigeo_id',
        'cargo',
        'fecha_ingreso',
        'estado',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'estado'           => 'boolean',
        'fecha_nacimiento' => 'date',
        'fecha_ingreso'    => 'date',
    ];

    /* ------------------------------------------------------------------ *
     * Relaciones
     * ------------------------------------------------------------------ */

    /** Distrito de residencia (nivel más específico del ubigeo). */
    public function ubigeo(): BelongsTo
    {
        return $this->belongsTo(Distrito::class, 'ubigeo_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'personal_id');
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
     * Accesores
     * ------------------------------------------------------------------ */

    protected function nombreCompleto(): Attribute
    {
        return Attribute::get(fn () => trim($this->nombres . ' ' . $this->apellidos));
    }

    /* ------------------------------------------------------------------ *
     * Scopes
     * ------------------------------------------------------------------ */

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', true);
    }
}
