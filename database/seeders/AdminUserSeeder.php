<?php

namespace Database\Seeders;

use App\Models\Admin\Menu;
use App\Models\Admin\Perfil;
use App\Models\Admin\Permiso;
use App\Models\Configuracion\Personal;
use App\Models\User;
use App\Services\SessionDataService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Usuario administrador inicial y asignación masiva de permisos.
 *
 * Mejora sobre FlowStock (deuda D14): la contraseña ya no está fija en el código.
 * Se toma de UROCENTER_ADMIN_PASSWORD; si no existe, se genera una aleatoria y se
 * muestra en consola una sola vez.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('UROCENTER_ADMIN_EMAIL', 'admin@urocenter.test');
        $password = env('UROCENTER_ADMIN_PASSWORD');
        $passwordGenerado = false;

        if (! $password) {
            $password = Str::password(14);
            $passwordGenerado = true;
        }

        // 1. Datos personales del administrador
        $personal = Personal::firstOrCreate(
            ['numero_documento' => '12345678'],
            [
                'tipo_documento' => 'DNI',
                'nombres'        => 'Administrador',
                'apellidos'      => 'del Sistema',
                'email_personal' => $email,
                'cargo'          => 'Administrador del sistema',
                'estado'         => true,
            ]
        );

        $perfilAdmin = Perfil::where('nombre', 'Administrador')->first();

        // 2. Usuario (la contraseña solo se define al crearlo)
        $usuario = User::firstOrCreate(
            ['email' => $email],
            [
                'name'        => trim($personal->nombres . ' ' . $personal->apellidos),
                'password'    => Hash::make($password),
                'personal_id' => $personal->id,
                'perfil_id'   => $perfilAdmin?->id,
                'estado'      => true,
            ]
        );

        if (! $usuario->personal_id) {
            $usuario->update(['personal_id' => $personal->id]);
        }

        // 3. Perfil superadministrador
        if ($perfilAdmin) {
            $usuario->perfiles()->syncWithoutDetaching([$perfilAdmin->id]);

            // 4. Asignación masiva: todos los permisos sobre todos los menús
            $menuIds = Menu::query()->pluck('id');
            $permisoIds = Permiso::query()->pluck('id');

            foreach ($menuIds as $menuId) {
                foreach ($permisoIds as $permisoId) {
                    DB::table('perfil_menu_permiso')->updateOrInsert(
                        [
                            'perfil_id'  => $perfilAdmin->id,
                            'menu_id'    => $menuId,
                            'permiso_id' => $permisoId,
                        ],
                        [
                            'created_by' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }

        // 5. Invalidar el contexto RBAC cacheado
        SessionDataService::invalidar();

        $this->command?->info('Usuario administrador: ' . $email);

        if ($passwordGenerado) {
            $this->command?->warn('Contraseña generada automáticamente (guárdela ahora): ' . $password);
        }
    }
}
