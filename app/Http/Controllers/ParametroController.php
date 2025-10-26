<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\StoreParametroRequest;
use App\Services\ParametroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParametroController extends Controller
{
    protected $parametroService;

    public function __construct(ParametroService $parametroService)
    {
        $this->parametroService = $parametroService;
    }

    public function index(): JsonResponse
    {
        try {
            $result = $this->parametroService->getAllParametros();

            return response()->json([
                'success' => true,
                'data' => $result['parametros'],
                'total' => $result['total'],
                'message' => $result['message']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los parámetros: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getByOlimpiada(int $idOlimpiada): JsonResponse
    {
        try {
            $result = $this->parametroService->getParametrosByOlimpiada($idOlimpiada);

            return response()->json([
                'success' => true,
                'data' => $result['parametros'],
                'total' => $result['total'],
                'message' => $result['message']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los parámetros: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreParametroRequest $request): JsonResponse
    {
        try {
            $result = $this->parametroService->createOrUpdateParametros($request->validated());

            $response = [
                'success' => true,
                'data' => $result['parametros_actualizados'],
                'total_procesados' => $result['total_procesados'],
                'message' => $result['message']
            ];

            if (isset($result['errors'])) {
                $response['errors'] = $result['errors'];
            }

            return response()->json($response, 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar los parámetros: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getParametrosGestionActual(): JsonResponse
    {
        try {
            $gestionActual = date('Y');
            
            $olimpiadaService = app(\App\Services\OlimpiadaService::class);
            $olimpiadaActual = $olimpiadaService->obtenerOlimpiadaActual();
            
            $result = $this->parametroService->getParametrosByOlimpiada($olimpiadaActual->id_olimpiada);

            return response()->json([
                'success' => true,
                'data' => $result['parametros'],
                'total' => $result['total'],
                'olimpiada_actual' => $olimpiadaActual->gestion,
                'message' => $result['message']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los parámetros: ' . $e->getMessage()
            ], 500);
        }
    }
}