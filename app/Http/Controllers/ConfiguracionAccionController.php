<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\ConfiguracionAccion\UpdateConfiguracionAccionRequest;
use App\Services\ConfiguracionAccionService;
use App\Services\UserActionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class ConfiguracionAccionController extends Controller
{
    public function __construct(
        protected ConfiguracionAccionService $service,
        protected UserActionService $gate
    ) {}

    /**
     * GET /api/configuracion-acciones
     * Devuelve la matriz completa (autogenerando lo que falte).
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $matriz = $this->service->obtenerMatrizCompleta();

            return response()->json([
                'status' => 'success',
                'data' => $matriz
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/configuracion-acciones
     * Guarda los cambios de los checkboxes.
     */
    public function update(UpdateConfiguracionAccionRequest $request): JsonResponse
    {
        // 1. Verificar Permiso de Admin (Gate Manual)
        if (!$this->gate->can($request->user_id, 'CONFIGURAR_SISTEMA')) {
            return response()->json(['message' => 'No tienes permiso para configurar el sistema.'], 403);
        }

        try {
            // 2. Delegar al servicio
            $this->service->actualizarMatriz(
                $request->user_id,
                $request->input('accionesPorFase')
            );

            return response()->json([
                'success' => true,
                'message' => 'Configuración actualizada correctamente.'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }
}
