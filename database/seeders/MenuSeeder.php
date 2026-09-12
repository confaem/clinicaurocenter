<?php

namespace Database\Seeders;

use App\Models\Admin\Menu;
use Illuminate\Database\Seeder;

/**
 * Árbol de menús de Clínica UroCenter (doc 01 §36).
 *
 * Idempotente (updateOrCreate por nombre + padre), a diferencia del MenuSeeder de
 * FlowStock que hacía `truncate` y `SET FOREIGN_KEY_CHECKS=0` (deuda D9).
 *
 * `nivel` es un código jerárquico por concatenación: 01, 02, 0201, 020101.
 * `enlace` es el nombre de ruta Laravel; si es null el ítem se muestra como
 * pendiente (enlace inactivo) hasta que el módulo se implemente.
 */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $estructura = [
            [
                'bloque' => 'PRINCIPAL',
                'nombre' => 'Dashboard',
                'icono'  => 'ti ti-layout-board',
                'enlace' => 'dashboard',
            ],
            [
                'bloque' => 'CLINICO',
                'nombre' => 'Clínico',
                'icono'  => 'ti ti-stethoscope',
                'hijos'  => [
                    ['nombre' => 'Pacientes',       'icono' => 'ti ti-users',        'enlace' => null],
                    ['nombre' => 'Admisión',        'icono' => 'ti ti-clipboard-plus', 'enlace' => null],
                    ['nombre' => 'Citas',           'icono' => 'ti ti-calendar-time', 'enlace' => null],
                    ['nombre' => 'Consulta Externa', 'icono' => 'ti ti-stethoscope', 'enlace' => null],
                    ['nombre' => 'Tópico',          'icono' => 'ti ti-first-aid-kit', 'enlace' => null],
                    ['nombre' => 'Procedimientos',  'icono' => 'ti ti-medical-cross', 'enlace' => null],
                    ['nombre' => 'Médicos',         'icono' => 'ti ti-user-heart',   'enlace' => null],
                    ['nombre' => 'Especialidades',  'icono' => 'ti ti-checklist',    'enlace' => null],
                    ['nombre' => 'Consultorios',    'icono' => 'ti ti-door',         'enlace' => null],
                    ['nombre' => 'Diagnósticos CIE-10', 'icono' => 'ti ti-book',     'enlace' => null],
                    ['nombre' => 'Controles',       'icono' => 'ti ti-repeat',       'enlace' => null],
                ],
            ],
            [
                'bloque' => 'COMERCIAL',
                'nombre' => 'Comercial',
                'icono'  => 'ti ti-shopping-cart',
                'hijos'  => [
                    [
                        'nombre' => 'POS',
                        'icono'  => 'ti ti-device-desktop',
                        'hijos'  => [
                            ['nombre' => 'Consulta',       'enlace' => null],
                            ['nombre' => 'Procedimientos', 'enlace' => null],
                            ['nombre' => 'Farmacia',       'enlace' => null],
                        ],
                    ],
                    ['nombre' => 'Ventas',       'icono' => 'ti ti-receipt',      'enlace' => null],
                    ['nombre' => 'Facturación',  'icono' => 'ti ti-file-invoice', 'enlace' => null],
                    ['nombre' => 'Caja',         'icono' => 'ti ti-cash-banknote', 'enlace' => null],
                    ['nombre' => 'Farmacia',     'icono' => 'ti ti-prescription', 'enlace' => null],
                    ['nombre' => 'Inventario',   'icono' => 'ti ti-packages',     'enlace' => null],
                ],
            ],
            [
                'bloque' => 'ADMIN',
                'nombre' => 'Administración',
                'icono'  => 'ti ti-settings',
                'hijos'  => [
                    ['nombre' => 'Usuarios',   'icono' => 'ti ti-user-cog',   'enlace' => null],
                    ['nombre' => 'Perfiles',   'icono' => 'ti ti-users-group', 'enlace' => null],
                    ['nombre' => 'Permisos',   'icono' => 'ti ti-key',        'enlace' => null],
                    ['nombre' => 'Menús',      'icono' => 'ti ti-list-tree',  'enlace' => null],
                    [
                        'nombre' => 'Configuración',
                        'icono'  => 'ti ti-adjustments',
                        'hijos'  => [
                            ['nombre' => 'Empresa',    'enlace' => 'configuracion.empresa.index'],
                            ['nombre' => 'Parámetros', 'enlace' => null],
                            ['nombre' => 'Terminales', 'enlace' => null],
                            ['nombre' => 'Personal',   'enlace' => null],
                            ['nombre' => 'Ubigeo',     'enlace' => null],
                        ],
                    ],
                ],
            ],
            [
                'bloque' => 'REPORTES',
                'nombre' => 'Reportes',
                'icono'  => 'ti ti-chart-histogram',
                'hijos'  => [
                    ['nombre' => 'Reportes médicos',    'icono' => 'ti ti-report-medical', 'enlace' => null],
                    ['nombre' => 'Reportes comerciales', 'icono' => 'ti ti-report-money',   'enlace' => null],
                ],
            ],
        ];

        $this->sembrar($estructura);
    }

    /**
     * Inserta recursivamente los niveles del menú.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    private function sembrar(array $items, ?int $parentId = null, string $prefijo = '', ?string $bloque = null): void
    {
        foreach ($items as $indice => $item) {
            $nivel = $prefijo . str_pad((string) ($indice + 1), 2, '0', STR_PAD_LEFT);

            $menu = Menu::updateOrCreate(
                ['nombre' => $item['nombre'], 'parent_id' => $parentId],
                [
                    'bloque'    => $parentId === null ? ($item['bloque'] ?? $bloque) : null,
                    'nivel'     => $nivel,
                    'enlace'    => $item['enlace'] ?? null,
                    'icono'     => $item['icono'] ?? null,
                    'orden'     => $indice + 1,
                    'estado'    => true,
                    'updated_by' => null,
                ]
            );

            if (! empty($item['hijos'])) {
                $this->sembrar($item['hijos'], $menu->id, $nivel, $item['bloque'] ?? $bloque);
            }
        }
    }
}
