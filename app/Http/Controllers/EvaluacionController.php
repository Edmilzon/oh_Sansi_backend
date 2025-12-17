<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\Evaluacion\BloquearFichaRequest;
use App\Http\Requests\Evaluacion\DesbloquearFichaRequest;
use App\Http\Requests\Evaluacion\GuardarNotaRequest;
use App\Http\Requests\Evaluacion\DescalificarCompetidorRequest;
use App\Services\EvaluacionService;
use Illuminate\Http\JsonResponse;
use Exception;

class EvaluacionController extends Controller
{
    public function __construct(
        protected EvaluacionService $service
    ) {}

    public function index(int $idExamen): JsonResponse
    {
        try {
            $data = $this->service->obtenerPizarraExamen($idExamen);
            return response()->json($data);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al cargar pizarra', 'error' => $e->getMessage()], 404);
        }
    }

    public function bloquear(BloquearFichaRequest $request, int $id): JsonResponse
    {
        try {
            $evaluacion = $this->service->bloquearFicha($id, $request->input('user_id'));
            return response()->json(['message' => 'Ficha bloqueada.', 'data' => $evaluacion]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        }
    }

    public function guardarNota(GuardarNotaRequest $request, int $id): JsonResponse
    {
        try {
            $evaluacion = $this->service->guardarNota($id, $request->validated());
            return response()->json(['message' => 'Calificación guardada.', 'data' => $evaluacion]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function desbloquear(DesbloquearFichaRequest $request, int $id): JsonResponse
    {
        try {
            $this->service->desbloquearFicha($id, $request->input('user_id'));
            return response()->json(['message' => 'Ficha liberada.']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Descalificar
     */
    public function descalificar(DescalificarCompetidorRequest $request, int $id): JsonResponse
    {
        try {
            $evaluacion = $this->service->descalificarCompetidor(
                $id,
                $request->input('user_id'),
                $request->input('motivo')
            );
            return response()->json(['message' => 'Competidor descalificado correctamente.', 'data' => $evaluacion]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Endpoint para el menú del Juez: Muestra dónde puede evaluar.
     */
    public function listarAreasNiveles(int $idUsuario): JsonResponse
    {
        try {
            $data = $this->service->listarAreasNivelesParaEvaluador($idUsuario);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
