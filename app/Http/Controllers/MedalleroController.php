<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use App\Services\MedalleroService;
use App\Models\ParametroMedallero;
use InvalidArgumentException;

class MedalleroController extends Controller
{
    protected MedalleroService $medalleroService;

    public function __construct(MedalleroService $medalleroService)
    {
        $this->medalleroService = $medalleroService;
    }
    public function getAreaPorResponsable(Request $request, $idResponsable): JsonResponse
    {
        $idResponsable = (int) $idResponsable;
        $areas = $this->medalleroService->getAreaPorResponsable($idResponsable);

        return response()->json([
            'success' => true,
            'data' => ['areas' => $areas]
        ], 200);
    }

    public function getNivelesPorArea(Request $request, $idArea): JsonResponse
{
    $idArea = (int) $idArea;
    $niveles = $this->medalleroService->getNivelesPorArea($idArea);

    return response()->json([
        'success' => true,
        'data' => ['niveles' => $niveles]
    ], 200);
}
public function guardarConfiguracionMedallero(Request $request)
{
    $validated = $request->validate([
        'niveles' => 'required|array',
        'niveles.*.id_area_nivel' => 'required|integer|exists:area_nivel,id_area_nivel',
        'niveles.*.oro' => 'required|integer|min:0',
        'niveles.*.plata' => 'required|integer|min:0',
        'niveles.*.bronce' => 'required|integer|min:0',
        'niveles.*.menciones' => 'required|integer|min:0',
    ]);

    foreach ($validated['niveles'] as $nivelData) {
        ParametroMedallero::updateOrCreate(
            ['id_area_nivel' => $nivelData['id_area_nivel']],
            [
                'oro' => $nivelData['oro'],
                'plata' => $nivelData['plata'],
                'bronce' => $nivelData['bronce'],
                'menciones' => $nivelData['menciones'],
            ]
        );
    }

    return response()->json(['message' => 'Configuración guardada correctamente.']);
}

}