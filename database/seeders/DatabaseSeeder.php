<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Semilla base de Clínica UroCenter.
 *
 * Orden obligatorio por dependencias:
 *   permisos → perfiles → menú → usuario admin (+ permisos del perfil) → ubigeo
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermisoSeeder::class,
            PerfilSeeder::class,
            MenuSeeder::class,
            AdminUserSeeder::class,
            UbigeoSeeder::class,
        ]);
    }
}
