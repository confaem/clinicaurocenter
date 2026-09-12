<?php

namespace App\Http\Controllers\Configuracion;

use App\Helpers\PermisoHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Configuracion\EmpresaRequest;
use App\Models\Configuracion\Empresa;
use App\Services\SessionDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * CRUD de Empresa — implementación canónica del contrato AJAX.
 *
 * Métodos: index / getData / store / edit / update / toggle
 * Ver docs/analisis/analisis-arquitectura-flowstock.md §4.
 */
class EmpresaController extends Controller
{
    /** Clave de autorización del módulo (menu.enlace). */
    private const RUTA = 'configuracion.empresa.index';

    /**
     * Vista principal (tarjeta + tabla + modales).
     */
    public function index()
    {
        if (! PermisoHelper::puede(self::RUTA, 'ver')) {
            abort(403, 'No tiene permisos para ver el módulo de Empresas.');
        }

        return view('components.layouts.configuracion.empresa.index');
    }

    /**
     * Datos para la tabla (AJAX).
     */
    public function getData(): JsonResponse
    {
        if (! PermisoHelper::puede(self::RUTA, 'ver')) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $empresas = Empresa::query()
            ->with('creator:id,name')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $empresas,
            'can'  => auth()->user()->permisosPorMenu(self::RUTA),
        ]);
    }

    /**
     * Registra una empresa.
     */
    public function store(EmpresaRequest $request): JsonResponse
    {
        if (! PermisoHelper::puede(self::RUTA, 'crear')) {
            return response()->json(['success' => false, 'message' => 'No tiene permiso para registrar empresas.'], 403);
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();
            unset($data['logo_file']);
            $data['created_by'] = auth()->id();

            if ($request->hasFile('logo_file')) {
                $data['logo'] = $request->file('logo_file')->store('empresas', 'public');
            }

            $empresa = Empresa::create($data);

            DB::commit();
            SessionDataService::invalidar();

            return response()->json([
                'success' => true,
                'message' => 'Empresa registrada correctamente.',
                'data'    => $empresa,
            ]);
        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Datos de una empresa para el modal de edición (AJAX).
     */
    public function edit(string $id): JsonResponse
    {
        if (! PermisoHelper::puede(self::RUTA, 'editar')) {
            return response()->json(['success' => false, 'message' => 'No tiene permiso para editar esta información.'], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => Empresa::findOrFail($id),
        ]);
    }

    /**
     * Actualiza una empresa.
     */
    public function update(EmpresaRequest $request, string $id): JsonResponse
    {
        if (! PermisoHelper::puede(self::RUTA, 'editar')) {
            return response()->json(['success' => false, 'message' => 'No tiene permiso para editar empresas.'], 403);
        }

        try {
            DB::beginTransaction();

            $empresa = Empresa::findOrFail($id);

            $data = $request->validated();
            unset($data['logo_file']);
            $data['updated_by'] = auth()->id();

            if ($request->hasFile('logo_file')) {
                if ($empresa->logo && Storage::disk('public')->exists($empresa->logo)) {
                    Storage::disk('public')->delete($empresa->logo);
                }

                $data['logo'] = $request->file('logo_file')->store('empresas', 'public');
            }

            $empresa->update($data);

            DB::commit();
            SessionDataService::invalidar();

            return response()->json([
                'success' => true,
                'message' => 'Empresa actualizada correctamente.',
            ]);
        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Activa / desactiva una empresa (baja lógica mediante `estado`).
     */
    public function toggle(string $id): JsonResponse
    {
        $empresa = Empresa::findOrFail($id);
        $accion = $empresa->estado ? 'desactivar' : 'activar';

        if (! PermisoHelper::puede(self::RUTA, $accion)) {
            return response()->json(['success' => false, 'message' => 'No tiene permiso para ' . $accion . ' empresas.'], 403);
        }

        try {
            $empresa->update([
                'estado'     => ! $empresa->estado,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Empresa ' . ($empresa->estado ? 'activada' : 'desactivada') . ' correctamente.',
            ]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
