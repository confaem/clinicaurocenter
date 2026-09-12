<?php

namespace Database\Seeders;

use App\Models\Configuracion\Departamento;
use App\Models\Configuracion\Distrito;
use App\Models\Configuracion\Pais;
use App\Models\Configuracion\Provincia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Maestro de ubigeo del Perú (departamentos → provincias → distritos).
 *
 * Fuente: dataset SUNAT en `database/seeders/{departamentos,provincias,distritos}.json`.
 * Idempotente: usa firstOrCreate y solo inserta distritos si la tabla está vacía.
 * `created_by` queda en null (la columna es nullable) para no depender del usuario 1.
 */
class UbigeoSeeder extends Seeder
{
    public function run(): void
    {
        $pathDeps  = database_path('seeders/departamentos.json');
        $pathProvs = database_path('seeders/provincias.json');
        $pathDists = database_path('seeders/distritos.json');

        foreach ([$pathDeps, $pathProvs, $pathDists] as $path) {
            if (! File::exists($path)) {
                $this->command?->error('No se encontró el archivo de ubigeo: ' . $path);

                return;
            }
        }

        $peru = Pais::firstOrCreate(
            ['codigo' => 'PE'],
            ['nombre' => 'Perú', 'es_peru' => true, 'estado' => true]
        );

        $jsonDeps  = json_decode(File::get($pathDeps), true);
        $jsonProvs = json_decode(File::get($pathProvs), true);
        $jsonDists = json_decode(File::get($pathDists), true);

        // ---------------------------------------------------------------
        // 1. Departamentos
        // ---------------------------------------------------------------
        $mapDepartamentoId = [];
        $mapDepartamentoCodigo = [];

        foreach ($jsonDeps as $dep) {
            $codigo = trim((string) $dep['codigo_ubigeo']);

            $modelo = Departamento::firstOrCreate(
                ['codigo' => $codigo, 'pais_id' => $peru->id],
                ['nombre' => trim((string) $dep['nombre_ubigeo']), 'estado' => true]
            );

            $mapDepartamentoId[$dep['id_ubigeo']] = $modelo->id;
            $mapDepartamentoCodigo[$dep['id_ubigeo']] = $codigo;
        }

        // El dataset SUNAT ubica la Provincia Constitucional del Callao (id 3285) dentro de Lima
        $callao = Departamento::firstOrCreate(
            ['codigo' => '07', 'pais_id' => $peru->id],
            ['nombre' => 'Callao', 'estado' => true]
        );

        // ---------------------------------------------------------------
        // 2. Provincias
        // ---------------------------------------------------------------
        $mapProvinciaId = [];
        $mapProvinciaCodigo = [];

        foreach ($jsonProvs as $departamentoJsonId => $provincias) {
            $departamentoId = $mapDepartamentoId[$departamentoJsonId] ?? null;
            $departamentoCodigo = $mapDepartamentoCodigo[$departamentoJsonId] ?? '';

            if (! $departamentoId) {
                continue;
            }

            foreach ($provincias as $prov) {
                $codigo = trim((string) $prov['codigo_ubigeo']);

                $padreId = $departamentoId;
                $padreCodigo = $departamentoCodigo;

                if ($prov['id_ubigeo'] === '3285' && mb_strtolower(trim((string) $prov['nombre_ubigeo'])) === 'callao') {
                    $padreId = $callao->id;
                    $padreCodigo = '07';
                }

                $modelo = Provincia::firstOrCreate(
                    ['codigo' => $codigo, 'departamento_id' => $padreId],
                    ['nombre' => trim((string) $prov['nombre_ubigeo']), 'estado' => true]
                );

                $mapProvinciaId[$prov['id_ubigeo']] = $modelo->id;
                $mapProvinciaCodigo[$prov['id_ubigeo']] = $padreCodigo . $codigo;
            }
        }

        // ---------------------------------------------------------------
        // 3. Distritos (inserción por lotes; se omite si ya existen)
        // ---------------------------------------------------------------
        if (Distrito::query()->exists()) {
            $this->command?->info('Los distritos ya estaban cargados: se omite la inserción.');

            return;
        }

        $lote = [];
        $ahora = now();

        foreach ($jsonDists as $provinciaJsonId => $distritos) {
            $provinciaId = $mapProvinciaId[$provinciaJsonId] ?? null;
            $provinciaCodigo = $mapProvinciaCodigo[$provinciaJsonId] ?? '';

            if (! $provinciaId) {
                continue;
            }

            foreach ($distritos as $dist) {
                $codigo = trim((string) $dist['codigo_ubigeo']);

                $lote[] = [
                    'codigo'       => $codigo,
                    'nombre'       => trim((string) $dist['nombre_ubigeo']),
                    'provincia_id' => $provinciaId,
                    'ubigeo'       => $provinciaCodigo . $codigo,
                    'estado'       => true,
                    'created_by'   => null,
                    'updated_by'   => null,
                    'created_at'   => $ahora,
                    'updated_at'   => $ahora,
                ];
            }
        }

        foreach (array_chunk($lote, 500) as $chunk) {
            DB::table('distritos')->insert($chunk);
        }

        $this->command?->info('Ubigeo del Perú cargado: ' . count($lote) . ' distritos.');
    }
}
