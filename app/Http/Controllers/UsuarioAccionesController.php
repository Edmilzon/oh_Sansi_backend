<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Services\UsuarioAccionesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class UsuarioAccionesController extends Controller
{
    public function __construct(
        protected UsuarioAccionesService $service
    ) {}

    /**
     * GET /api/usuario/mis-acciones/usuario/{id_user}
     */
    public function misAcciones(Request $request, int $id_user): JsonResponse
    {
        try {
            $data = $this->service->obtenerDetalleCapacidades($id_user);

            return response()->json([
                'success' => true,
                'data'    => $data,
                'message' => 'Permisos calculados estrictamente (Intersección Rol y Fase).'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
