<?php

namespace App\Http\Controllers;

use App\Services\AreaOlimpiadaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class AreaOlimpiadaController extends Controller
{
    protected $areaOlimpiadaService;

    public function __construct(AreaOlimpiadaService $areaOlimpiadaService)
    {
        $this->areaOlimpiadaService = $areaOlimpiadaService;
    }

    /**
     * Obtiene todas las áreas asociadas a una olimpiada.
     *
     * @param int|string $identifier
     * @return JsonResponse
     */
    public function getAreasByOlimpiada(int|string $identifier): JsonResponse
    {
        try {
            $areas = $this->areaOlimpiadaService->getAreasByOlimpiada($identifier);

            return response()->json([
                'message' => 'Áreas obtenidas exitosamente para la olimpiada.',
                'data' => $areas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las áreas de la olimpiada.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
