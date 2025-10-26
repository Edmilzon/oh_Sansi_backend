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

    public function getAreasGestionActual(): JsonResponse
    {
        try {
            $areas = $this->areaOlimpiadaService->getAreasGestionActual();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'areas' => $areas
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las áreas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getNombresAreasGestionActual(): JsonResponse
    {
        try {
            $nombresAreas = $this->areaOlimpiadaService->getNombresAreasGestionActual();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'nombres_areas' => $nombresAreas
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los nombres de las áreas: ' . $e->getMessage()
            ], 500);
        }
    }
}
