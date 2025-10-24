<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AreaNivelService;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AreaNivelController extends Controller
{
    protected $areaNivelService;

    public function __construct(AreaNivelService $areaNivelService)
    {
        $this->areaNivelService = $areaNivelService;
    }

    public function index(): JsonResponse
    {
        try {
            $areaNiveles = $this->areaNivelService->getAreaNivelList();
            return response()->json([
                'success' => true,
                'data' => $areaNiveles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las relaciones área-nivel: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function store(Request $request): JsonResponse
    {
        try {
            \Log::info('[CONTROLLER] Request recibido en store:', [
                'full_url' => $request->fullUrl(),
                'method' => $request->method(),
                'content_type' => $request->header('Content-Type'),
                'all_data' => $request->all(),
                'data_count' => count($request->all()),
                'json_data' => $request->getContent()
            ]);

            $validatedData = $request->validate([
                '*.id_area' => 'required|integer',
                '*.id_nivel' => 'required|integer',
                '*.activo' => 'required|boolean'
            ]);

            \Log::info('[CONTROLLER] Datos validados:', [
                'validated_data' => $validatedData, 
                'validated_count' => count($validatedData),
                'validated_structure' => $validatedData[0] ?? 'no first element'
            ]);

            $result = $this->areaNivelService->createMultipleAreaNivel($validatedData);
            
            $response = [
                'success' => true,
                'data' => $result['area_niveles'],
                'message' => $result['message'],
                'olimpiada_actual' => $result['olimpiada'],
                'success_count' => $result['success_count'],
                'created_count' => count($result['area_niveles'])
            ];

            if (!empty($result['errors'])) {
                $response['errors'] = $result['errors'];
                $response['error_count'] = $result['error_count'];
            }

            \Log::info('[CONTROLLER] Response enviado:', $response);
            return response()->json($response, 201);
            
        } catch (ValidationException $e) {
            \Log::error('[CONTROLLER] Error de validación:', [
                'validation_errors' => $e->errors(),
                'request_data' => $request->all(),
                'request_count' => count($request->all())
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error de validación en los datos',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('[CONTROLLER] Error general en store:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear las relaciones área-nivel: ' . $e->getMessage()
            ], 400);
        }
    }

    public function getByArea($id_area): JsonResponse
    {
        try {
            $areaNiveles = $this->areaNivelService->getAreaNivelByArea($id_area);
            return response()->json([
                'success' => true,
                'data' => $areaNiveles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las relaciones área-nivel: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getByAreaAll($id_area): JsonResponse
    {
        try {
            $areaNiveles = $this->areaNivelService->getAreaNivelByAreaAll($id_area);
            return response()->json([
                'success' => true,
                'data' => $areaNiveles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las relaciones área-nivel a detalle: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAreasConNiveles(): JsonResponse
    {
        try {
            $result = $this->areaNivelService->getAreaNivelesAsignadosAll();
    
            return response()->json([
                'success' => true,
                'data' => $result['areas'],
                'olimpiada_actual' => $result['olimpiada_actual'],
                'message' => $result['message']
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener áreas con niveles: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $result = $this->areaNivelService->getAreaNivelById($id);
            
            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Relación área-nivel no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $result['area_nivel'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la relación área-nivel: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateByArea($id_area, Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                '*.id_nivel' => 'required|integer|exists:nivel,id_nivel',
                '*.activo' => 'required|boolean'
            ]);

            $result = $this->areaNivelService->updateAreaNivelByArea($id_area, $validatedData);
            
            return response()->json([
                'success' => true,
                'data' => $result['area_niveles'],
                'olimpiada_actual' => $result['olimpiada'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar las relaciones área-nivel: ' . $e->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'id_area' => 'sometimes|required|integer|exists:area,id_area',
                'id_nivel' => 'sometimes|required|integer|exists:nivel,id_nivel',
                'activo' => 'sometimes|required|boolean'
            ]);

            $result = $this->areaNivelService->updateAreaNivel($id, $validatedData);
            
            return response()->json([
                'success' => true,
                'data' => $result['area_nivel'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la relación área-nivel: ' . $e->getMessage()
            ], 400);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $result = $this->areaNivelService->deleteAreaNivel($id);
            
            return response()->json([
                'success' => true,
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la relación área-nivel: ' . $e->getMessage()
            ], 400);
        }
    }
}