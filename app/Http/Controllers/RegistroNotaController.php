<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RegistroNotaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class RegistroNotaController extends Controller
{
    protected $registroNotaService;

    public function __construct(RegistroNotaService $registroNotaService)
    {
        $this->registroNotaService = $registroNotaService;
    }

    /**
     * Obtener historial de calificaciones con filtros y paginación
     */
    public function getHistorialCalificaciones(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'id_area' => 'nullable|integer|exists:area,id_area',
                'ids_niveles' => 'nullable|string', // Espera string separado por comas
                'page' => 'required|integer|min:1',
                'limit' => 'required|integer|min:1|max:100',
            ]);

            // Convertir string de IDs a array
            $ids_niveles = null;
            if (!empty($validatedData['ids_niveles'])) {
                $ids_niveles = array_map('intval', explode(',', $validatedData['ids_niveles']));
            }

            $result = $this->registroNotaService->getHistorialCalificaciones(
                $validatedData['id_area'] ?? null,
                $ids_niveles,
                $validatedData['page'],
                $validatedData['limit']
            );

            return response()->json($result);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Error de validación en los parámetros',
                'errors' => $e->errors(),
                'meta' => [
                    'total' => 0,
                    'page' => $request->input('page', 1),
                    'limit' => $request->input('limit', 10),
                    'totalPages' => 0,
                ]
            ], 422);

        } catch (\Exception $e) {
            Log::error('[CONTROLLER] Error en getHistorialCalificaciones:', [
                'params' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Error interno del servidor al obtener el historial',
                'meta' => [
                    'total' => 0,
                    'page' => $request->input('page', 1),
                    'limit' => $request->input('limit', 10),
                    'totalPages' => 0,
                ]
            ], 500);
        }
    }

    /**
     * Obtener todas las áreas para el filtro
     */
    public function getAreasParaFiltro(): JsonResponse
    {
        try {
            $result = $this->registroNotaService->getAreasParaFiltro();

            $status = $result['success'] ? 200 : 400;

            return response()->json($result, $status);

        } catch (\Exception $e) {
            Log::error('[CONTROLLER] Error en getAreasParaFiltro:', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Error al obtener las áreas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener niveles por área para el filtro
     */
    public function getNivelesPorArea(int $id_area): JsonResponse
    {
        try {
            $result = $this->registroNotaService->getNivelesPorArea($id_area);

            $status = $result['success'] ? 200 : 400;

            return response()->json($result, $status);

        } catch (\Exception $e) {
            Log::error('[CONTROLLER] Error en getNivelesPorArea:', [
                'id_area' => $id_area,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Error al obtener los niveles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo registro de nota
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'id_area_nivel' => 'required|integer|exists:area_nivel,id_area_nivel',
                'id_evaluadorAN' => 'required|integer|exists:evaluador_an,id_evaluadorAN',
                'id_competidor' => 'required|integer|exists:competidor,id_competidor',
                'accion' => 'required|string|max:255',
                'nota_anterior' => 'nullable|numeric',
                'nota_nueva' => 'nullable|numeric',
                'observacion' => 'nullable|string',
                'descripcion' => 'required|string',
            ]);

            $result = $this->registroNotaService->createRegistroNota($validatedData);

            $status = $result['success'] ? 201 : 400;

            return response()->json($result, $status);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación en los datos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('[CONTROLLER] Error en store RegistroNota:', [
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el registro de nota: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un registro de nota específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $result = $this->registroNotaService->getRegistroNotaById($id);

            $status = $result['success'] ? 200 : 404;

            return response()->json($result, $status);

        } catch (\Exception $e) {
            Log::error('[CONTROLLER] Error en show RegistroNota:', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el registro de nota: ' . $e->getMessage()
            ], 500);
        }
    }
}