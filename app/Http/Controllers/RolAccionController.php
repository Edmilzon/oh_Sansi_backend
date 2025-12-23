<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Services\RolAccionService;
use App\Services\UserActionService;
use App\Http\Requests\RolAccion\UpdateGlobalRolAccionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class RolAccionController extends Controller
{
    public function __construct(
        protected RolAccionService $service,
        protected UserActionService $gate
    ) {}

    /**
     * GET /api/roles/matriz
     * Retorna TODOS los roles con sus acciones para editar permisos.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Seguridad de lectura (Opcional, pero recomendado)
            if ($request->has('user_id') && !$this->gate->esSuperAdmin($request->user_id)) {
                 return response()->json(['message' => 'Acceso denegado.'], 403);
            }

            $matrizGlobal = $this->service->obtenerMatrizGlobal();

            return response()->json([
                'success' => true,
                'data' => $matrizGlobal,
                'message' => 'Matriz global de permisos obtenida.'
            ]);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/roles/matriz
     * Guarda cambios masivos en los permisos de los roles.
     */
    public function updateGlobal(UpdateGlobalRolAccionRequest $request): JsonResponse
    {
        // 1. SEGURIDAD: LLAVE MAESTRA
        // Solo el Administrador puede dar o quitar permisos a otros roles.
        if (!$this->gate->esSuperAdmin($request->user_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado. Solo el Administrador puede gestionar roles.'
            ], 403);
        }

        try {
            // 2. Delegar la actualización masiva al servicio
            // CORRECCIÓN: Nombre del método 'updateGlobal' y solo un argumento (array de roles)
            $this->service->updateGlobal(
                $request->input('roles') // Array validado por el Request
            );

            return response()->json([
                'success' => true,
                'message' => 'Permisos de roles actualizados correctamente.'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar permisos: ' . $e->getMessage()
            ], 500);
        }
    }
}
