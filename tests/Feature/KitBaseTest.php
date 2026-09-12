<?php

namespace Tests\Feature;

use App\Models\Admin\Menu;
use App\Models\Admin\Perfil;
use App\Models\Admin\Permiso;
use App\Models\Configuracion\Empresa;
use App\Models\User;
use Database\Seeders\MenuSeeder;
use Database\Seeders\PerfilSeeder;
use Database\Seeders\PermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Verificación del kit base de Clínica UroCenter.
 *
 * Cubre las deudas D1 (migraciones de dominio registradas), D8 (superadmin sin
 * IDs fijos) y D13 (falta de pruebas) detectadas en el análisis de FlowStock.
 *
 * Las pruebas corren sobre SQLite en memoria (ver phpunit.xml): nunca tocan
 * la base de datos de desarrollo `clinica_urocenter`.
 */
class KitBaseTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'Secreto!2026';

    /* ------------------------------------------------------------------ *
     * Helpers
     * ------------------------------------------------------------------ */

    private function sembrarCatalogos(): void
    {
        $this->seed([
            PermisoSeeder::class,
            PerfilSeeder::class,
            MenuSeeder::class,
        ]);
    }

    private function crearUsuario(?Perfil $perfil = null, bool $estado = true): User
    {
        $usuario = User::create([
            'name'     => 'Usuario de prueba',
            'email'    => 'prueba' . uniqid() . '@urocenter.test',
            'password' => Hash::make(self::PASSWORD),
            'estado'   => $estado,
        ]);

        if ($perfil) {
            $usuario->perfiles()->attach($perfil->id);
        }

        return $usuario;
    }

    private function crearSuperadmin(): User
    {
        $perfil = Perfil::where('es_superadmin', true)->firstOrFail();

        return $this->crearUsuario($perfil);
    }

    /* ------------------------------------------------------------------ *
     * Migraciones y catálogos base
     * ------------------------------------------------------------------ */

    public function test_las_migraciones_de_dominio_se_ejecutan_en_base_vacia(): void
    {
        // RefreshDatabase ya migró; verificamos las tablas maestras clave
        foreach ([
            'users', 'personal', 'perfiles', 'permisos', 'menu',
            'perfil_user', 'perfil_menu_permiso',
            'empresa', 'parametros', 'terminal',
            'paises', 'departamentos', 'provincias', 'distritos',
        ] as $tabla) {
            $this->assertTrue(
                \Illuminate\Support\Facades\Schema::hasTable($tabla),
                "La tabla {$tabla} no existe."
            );
        }
    }

    public function test_los_seeders_crean_permisos_perfiles_y_menu(): void
    {
        $this->sembrarCatalogos();

        $this->assertSame(9, Permiso::count());
        $this->assertDatabaseHas('permisos', ['codigo' => 'acceso']);
        $this->assertDatabaseHas('perfiles', ['nombre' => 'Administrador', 'es_superadmin' => true]);
        $this->assertGreaterThan(20, Menu::count());
        $this->assertDatabaseHas('menu', ['nombre' => 'Empresa', 'enlace' => 'configuracion.empresa.index']);
    }

    /* ------------------------------------------------------------------ *
     * Autenticación
     * ------------------------------------------------------------------ */

    public function test_la_pantalla_de_acceso_responde_correctamente(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Ingresar');
    }

    public function test_un_usuario_puede_iniciar_sesion_y_llega_al_dashboard(): void
    {
        $this->sembrarCatalogos();
        $usuario = $this->crearUsuario(Perfil::where('nombre', 'Administrador')->first());

        $this->post('/login', [
            'email'    => $usuario->email,
            'password' => self::PASSWORD,
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_un_usuario_inactivo_es_expulsado(): void
    {
        $this->sembrarCatalogos();
        $usuario = $this->crearUsuario(null, estado: false);

        $this->actingAs($usuario)
            ->get('/dashboard')
            ->assertRedirect(route('login'));
    }

    public function test_el_contexto_rbac_se_carga_en_sesion(): void
    {
        $this->sembrarCatalogos();
        $usuario = $this->crearUsuario(Perfil::where('nombre', 'Administrador')->first());

        $this->actingAs($usuario)->get('/dashboard')->assertOk();

        $this->assertIsArray(session('usuario_permisos'));
        $this->assertIsArray(session('usuario_menu_tree'));
        $this->assertNotEmpty(session('usuario_menu_tree'));
    }

    /* ------------------------------------------------------------------ *
     * RBAC: control de acceso
     * ------------------------------------------------------------------ */

    public function test_sin_permiso_el_modulo_devuelve_403(): void
    {
        $this->sembrarCatalogos();
        $sinPermisos = $this->crearUsuario(Perfil::where('nombre', 'Recepción')->first());

        $this->actingAs($sinPermisos)->get('/configuracion/empresa')->assertForbidden();
        $this->actingAs($sinPermisos)->getJson('/configuracion/empresa/data')->assertForbidden();
    }

    public function test_el_superadmin_accede_sin_necesitar_filas_de_permiso(): void
    {
        $this->sembrarCatalogos();
        $superadmin = $this->crearSuperadmin();

        // El perfil es superadmin: no se asignó ninguna fila en perfil_menu_permiso
        $this->assertSame(0, \DB::table('perfil_menu_permiso')->count());

        $this->actingAs($superadmin)->get('/configuracion/empresa')->assertOk();
    }

    /* ------------------------------------------------------------------ *
     * CRUD de Empresa (contrato AJAX)
     * ------------------------------------------------------------------ */

    public function test_la_validacion_devuelve_422_por_campo(): void
    {
        $this->sembrarCatalogos();

        $this->actingAs($this->crearSuperadmin())
            ->postJson('/configuracion/empresa', ['ruc' => '', 'razon_social' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ruc', 'razon_social']);
    }

    public function test_flujo_completo_de_empresa(): void
    {
        $this->sembrarCatalogos();
        $usuario = $this->crearSuperadmin();

        // 1. Crear
        $this->actingAs($usuario)
            ->postJson('/configuracion/empresa', [
                'ruc'              => '20512345678',
                'razon_social'     => 'CLINICA UROCENTER S.A.C.',
                'nombre_comercial' => 'Clínica UroCenter',
                'telefono'         => '074123456',
                'email'            => 'contacto@urocenter.test',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $empresa = Empresa::firstOrFail();
        $this->assertSame($usuario->id, $empresa->created_by);

        // 2. RUC duplicado
        $this->actingAs($usuario)
            ->postJson('/configuracion/empresa', [
                'ruc'          => '20512345678',
                'razon_social' => 'Otra empresa',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ruc']);

        // 3. Listado con permisos (`can`)
        $this->actingAs($usuario)
            ->getJson('/configuracion/empresa/data')
            ->assertOk()
            ->assertJsonStructure(['data', 'can'])
            ->assertJsonPath('data.0.ruc', '20512345678');

        // 4. Editar (datos del modal)
        $this->actingAs($usuario)
            ->getJson('/configuracion/empresa/' . $empresa->id . '/edit')
            ->assertOk()
            ->assertJsonPath('data.nombre_comercial', 'Clínica UroCenter');

        // 5. Actualizar
        $this->actingAs($usuario)
            ->postJson('/configuracion/empresa/' . $empresa->id, [
                'id'               => $empresa->id,
                'ruc'              => '20512345678',
                'razon_social'     => 'CLINICA UROCENTER S.A.C.',
                'nombre_comercial' => 'UroCenter',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame('UroCenter', $empresa->fresh()->nombre_comercial);

        // 6. Activar / desactivar (baja lógica)
        $this->actingAs($usuario)
            ->postJson('/configuracion/empresa/' . $empresa->id . '/toggle')
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertFalse($empresa->fresh()->estado);

        $this->actingAs($usuario)
            ->postJson('/configuracion/empresa/' . $empresa->id . '/toggle')
            ->assertOk();

        $this->assertTrue($empresa->fresh()->estado);
    }
}
