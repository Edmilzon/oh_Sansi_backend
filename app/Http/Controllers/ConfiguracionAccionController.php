<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Services\ConfiguracionAccionService;
use App\Services\UserActionService;
use App\Http\Requests\ConfiguracionAccion\UpdateConfiguracionAccionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class ConfiguracionAccionController extends Controller
{
    public function __construct(
        protected ConfiguracionAccionService $service,
        protected UserActionService $gate
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            if ($request->has('user_id') && !$this->gate->esSuperAdmin($request->user_id)) {
                return response()->json(['message' => 'Acceso denegado.'], 403);
            }

            $matriz = $this->service->obtenerMatrizCompleta();

            return response()->json([
                'success' => true,
                'data' => $matriz,
                'message' => 'Matriz de configuración obtenida.'
            ]);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateConfiguracionAccionRequest $request): JsonResponse
    {
        if (!$this->gate->esSuperAdmin($request->input('user_id'))) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado. Solo el Administrador puede cambiar la configuración.'
            ], 403);
        }

        try {
            // CORRECCIÓN AQUÍ: Se llama a 'update' en lugar de 'actualizarConfiguracion'
            $this->service->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Configuración actualizada correctamente.'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar configuración: ' . $e->getMessage()
            ], 500);
        }
    }
}
