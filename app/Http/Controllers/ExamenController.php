<?php

namespace App\Http\Controllers;

use App\Http\Requests\Examen\StoreExamenRequest;
use App\Http\Requests\Examen\UpdateExamenRequest;
use App\Services\ExamenService;
use App\Model\Examen;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use App\Events\ExamenCreado;
use Exception;

class ExamenController extends Controller
{
    public function __construct(
        protected ExamenService $service
    ) {}

    public function index(int $competenciaId): JsonResponse
    {
        $examenes = Examen::where('id_competencia', $competenciaId)->get();
        return response()->json($examenes);
    }

    public function store(StoreExamenRequest $request): JsonResponse
    {
        try {
            $examen = $this->service->crearExamen($request->validated());
            broadcast(new ExamenCreado($examen))->toOthers();
            return response()->json(['message' => 'Examen creado.', 'data' => $examen], 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(Examen::with('competencia')->findOrFail($id));
    }

    public function update(UpdateExamenRequest $request, int $id): JsonResponse
    {
        try {
            $examen = $this->service->actualizarExamen($id, $request->validated());
            broadcast(new ExamenCreado($examen))->toOthers();
            return response()->json(['message' => 'Actualizado.', 'data' => $examen]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->eliminarExamen($id);
            return response()->json(['message' => 'Eliminado.']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function iniciar(int $id): JsonResponse
    {
        try {
            $examen = $this->service->iniciarExamen($id);
            return response()->json(['message' => 'Mesa abierta.', 'data' => $examen]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        }
    }

    public function finalizar(int $id): JsonResponse
    {
        try {
            $examen = $this->service->finalizarExamen($id);
            return response()->json(['message' => 'Examen cerrado.', 'data' => $examen]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        }
    }

    public function indexPorAreaNivel(int $idAreaNivel): JsonResponse
    {
        try {
            $examenes = $this->service->listarPorAreaNivel($idAreaNivel);
            return response()->json($examenes);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al obtener exámenes: ' . $e->getMessage()], 500);
        }
    }

    public function comboPorAreaNivel(int $idAreaNivel): JsonResponse
    {
        try {
            $data = $this->service->listarParaCombo($idAreaNivel);
            return response()->json($data);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Lista de competidores para la "Sala de Evaluación" o Pizarra.
     */
    public function competidoresPorExamen(int $id): JsonResponse
    {
        try {
            $lista = $this->service->listarCompetidores($id);
            return response()->json($lista);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
