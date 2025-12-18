<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\Fase\StoreFaseCompletaRequest;
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
            ], 409); // Conflict
        }
    }
}
