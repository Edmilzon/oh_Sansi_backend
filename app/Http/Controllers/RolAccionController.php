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

    public function index(Request $request): JsonResponse
    {
        try {

            $matrizGlobal = $this->service->obtenerMatrizGlobal();

            return response()->json([
                'success' => true,
                'data' => $matrizGlobal,
                'message' => 'Matriz global de permisos obtenida correctamente.'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener matriz: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateGlobal(UpdateGlobalRolAccionRequest $request): JsonResponse
    {
        if (!$this->gate->can($request->user_id, 'GESTIONAR_ROLES')) {
            return response()->json([
                'message' => 'Acceso denegado: No tienes permiso para gestionar roles.'
            ], 403);
        }

        try {
            $this->service->actualizarMatrizGlobal(
                $request->user_id,
                $request->input('roles')
            );

            return response()->json([
                'success' => true,
                'message' => 'Matriz de permisos actualizada correctamente.'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar configuración: ' . $e->getMessage()
            ], 500);
        }
    }
}
