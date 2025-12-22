<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\Reporte\GetHistorialRequest;
use App\Services\ReporteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function __construct(
        protected ReporteService $reporteService
    ) {}

    public function historialCalificaciones(GetHistorialRequest $request): JsonResponse
    {
        try {
            $data = $this->reporteService->generarHistorialPaginado(
                $request->validated()
            );

            return response()->json($data, 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno al generar el reporte de historial.',
                'debug'   => $e->getMessage()
            ], 500);
        }
    }

    public function historialCambios(int $idEvaluacion): JsonResponse
    {
        try {
            $data = $this->reporteService->obtenerHistorialEvaluacion($idEvaluacion);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getAreas(): JsonResponse
    {
        try {
            $areas = $this->reporteService->obtenerAreasFiltro();

            return response()->json([
                'data' => $areas
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getNivelesPorArea(int $idArea): JsonResponse
    {
        try {
            $niveles = $this->reporteService->obtenerNivelesFiltro($idArea);

            return response()->json([
                'data' => $niveles
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function ranking(int $idCompetencia): JsonResponse
    {
        try {
            $data = $this->reporteService->obtenerResultadosOficiales($idCompetencia);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function exportarGanadores(int $idCompetencia)
    {
        // TODO: Implementar exportación real usando Maatwebsite Excel o DomPDF
        return response()->json(['message' => 'Funcionalidad de exportación PDF pendiente de implementación.']);
    }
}
