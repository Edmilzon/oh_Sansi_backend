<?php

namespace App\Http\Controllers;

use App\Services\ReporteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ReporteController extends Controller
{
    public function __construct(
        protected ReporteService $service
    ) {}

    /**
     * GET /api/reportes/competencia/{id}/ranking
     * Muestra la lista oficial de resultados (Ya sea clasificados o medallero).
     */
    public function ranking(int $idCompetencia): JsonResponse
    {
        try {
            $data = $this->service->obtenerResultadosOficiales($idCompetencia);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * GET /api/reportes/evaluacion/{id}/historial
     * Muestra el log de auditoría de una nota específica.
     */
    public function historialCambios(int $idEvaluacion): JsonResponse
    {
        $historial = $this->service->obtenerHistorialEvaluacion($idEvaluacion);
        return response()->json($historial);
    }

    /**
     * GET /api/reportes/competencia/{id}/exportar-ganadores
     * (Futuro) Para descargar Excel/PDF.
     */
    public function exportarGanadores(int $idCompetencia)
    {
        return response()->json(['message' => 'Endpoint listo para implementación de PDF']);
    }
}
