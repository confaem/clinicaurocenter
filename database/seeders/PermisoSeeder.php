<?php

namespace Database\Seeders;

use App\Models\Admin\Permiso;
use Illuminate\Database\Seeder;

/**
 * Catálogo de acciones del RBAC.
 * `acceso` controla la visibilidad del menú; el resto, las operaciones del módulo.
 */
class PermisoSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            ['codigo' => 'acceso',     'nombre' => 'Acceso al Menú',  'descripcion' => 'Muestra el módulo en el menú lateral y permite entrar'],
            ['codigo' => 'ver',        'nombre' => 'Ver',             'descripcion' => 'Permite visualizar el módulo y sus listados'],
            ['codigo' => 'crear',      'nombre' => 'Crear',            'descripcion' => 'Permite registrar nuevos datos'],
            ['codigo' => 'editar',     'nombre' => 'Editar',           'descripcion' => 'Permite modificar datos existentes'],
            ['codigo' => 'eliminar',   'nombre' => 'Eliminar',         'descripcion' => 'Permite borrar registros'],
            ['codigo' => 'activar',    'nombre' => 'Activar',          'descripcion' => 'Permite habilitar registros'],
            ['codigo' => 'desactivar', 'nombre' => 'Desactivar',       'descripcion' => 'Permite deshabilitar registros'],
            ['codigo' => 'aprobar',    'nombre' => 'Aprobar',          'descripcion' => 'Permite aprobar documentos o procesos'],
            ['codigo' => 'exportar',   'nombre' => 'Exportar',         'descripcion' => 'Permite descargar reportes o datos'],
        ];

        foreach ($permisos as $permiso) {
            Permiso::updateOrCreate(
                ['codigo' => $permiso['codigo']],
                [
                    'nombre'      => $permiso['nombre'],
                    'descripcion' => $permiso['descripcion'],
                    'estado'      => true,
                ]
            );
        }
    }
}
