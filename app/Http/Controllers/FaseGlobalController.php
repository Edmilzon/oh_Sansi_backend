<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\Fase\StoreFaseCompletaRequest;
use App\Http\Requests\Cronograma\UpdateCronogramaRequest;
use App\Services\FaseGlobalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class FaseGlobalController extends Controller
{
    public function __construct(
        protected FaseGlobalService $service
    ) {}

    public function storeCompleto(StoreFaseCompletaRequest $request): JsonResponse
    {
        try {
            $resultado = $this->service->crearFaseCompleta($request->validated());

            return response()->json([
                'message' => 'Fase global y cronograma configurados correctamente.',
                'data' => $resultado
            ], 201);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'No se pudo configurar la fase.',
                'error' => $e->getMessage()
            ], 409);
        }
    }

    // GET /api/fase-global/actuales
    public function listarActuales(): JsonResponse
    {
        return response()->json($this->service->listarFasesActuales());
    }

    // GET /api/fase-global/{id}
    public function show(int $id): JsonResponse
    {
        try {
            $fase = $this->service->obtenerDetalleFase($id);
            return response()->json($fase);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Fase no encontrada'], 404);
        }
    }

    public function updateCronograma(UpdateCronogramaRequest $request, int $id): JsonResponse
    {
        // El controller llama a FaseGlobalService -> que llama a CronogramaFaseService
        $cronograma = $this->service->actualizarCronograma($id, $request->validated());

        return response()->json([
            'message' => 'Cronograma actualizado correctamente.',
            'data' => $cronograma
        ]);
    }
}
