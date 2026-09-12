<?php

namespace Database\Seeders;

use App\Models\Admin\Perfil;
use Illuminate\Database\Seeder;

/**
 * Perfiles (roles) iniciales — doc 01 §34.
 * `Administrador` es el único marcado como superadministrador (acceso total sin
 * depender de IDs fijos, corrigiendo la deuda D8 de FlowStock).
 */
class PerfilSeeder extends Seeder
{
    public function run(): void
    {
        $perfiles = [
            ['nombre' => 'Administrador', 'descripcion' => 'Acceso total al sistema', 'es_superadmin' => true],
            ['nombre' => 'Médico',        'descripcion' => 'Atención clínica: consulta, diagnósticos y procedimientos', 'es_superadmin' => false],
            ['nombre' => 'Recepción',     'descripcion' => 'Admisión, registro de pacientes y agenda de citas', 'es_superadmin' => false],
            ['nombre' => 'Cajero',        'descripcion' => 'Caja, cobros y comprobantes', 'es_superadmin' => false],
            ['nombre' => 'Farmacia',      'descripcion' => 'Dispensación y control de farmacia', 'es_superadmin' => false],
            ['nombre' => 'Almacén',       'descripcion' => 'Inventario, ingresos y salidas de almacén', 'es_superadmin' => false],
        ];

        foreach ($perfiles as $perfil) {
            Perfil::updateOrCreate(
                ['nombre' => $perfil['nombre']],
                [
                    'descripcion'   => $perfil['descripcion'],
                    'es_superadmin' => $perfil['es_superadmin'],
                    'estado'        => true,
                ]
            );
        }
    }
}
