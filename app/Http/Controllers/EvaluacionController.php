<?php

namespace App\Http\Controllers;

use App\Services\EvaluacionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class EvaluacionController extends Controller
{
    protected $evaluacionService;

    public function __construct(EvaluacionService $evaluacionService)
    {
        $this->evaluacionService = $evaluacionService;
    }

    /**
     * Almacena una nueva evaluación para un competidor en una competencia específica.
     *
     * @param Request $request
     * @param int $id_competencia
     * @return JsonResponse
     */
    public function store(Request $request, int $id_competencia): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nota' => 'required|numeric|min:0|max:100',
            'observaciones' => 'nullable|string',
            'id_competidor' => 'required|exists:competidor,id_competidor',
            'id_evaluadorAN' => 'required|exists:evaluador_an,id_evaluadorAN',
            'estado' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $datosEvaluacion = $request->all();
            $datosEvaluacion['fecha_evaluacion'] = now()->toDateString();

            $evaluacion = $this->evaluacionService->calificarCompetidor($datosEvaluacion, $id_competencia);

            return response()->json($evaluacion, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al registrar la calificación.', 'error' => $e->getMessage()], 500);
        }
    }
}
