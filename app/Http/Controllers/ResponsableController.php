<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Services\ResponsableService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ResponsableController extends Controller
{
    protected $responsableService;

    public function __construct(ResponsableService $responsableService)
    {
        $this->responsableService = $responsableService;
    }

    /**
     * Registra un nuevo usuario responsable de área.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'ci' => 'required|string|unique:usuario,ci',
            'email' => 'required|email|unique:usuario,email',
            'password' => 'required|string|min:8',
            'telefono' => 'nullable|string|max:20',
            'id_olimpiada' => 'required|integer|exists:olimpiada,id_olimpiada',
            'areas' => 'required|array|min:1',
            'areas.*' => 'integer|exists:area,id_area',
        ]);

        try {
            $responsableData = $request->only([
                'nombre', 'apellido', 'ci', 'email', 'password', 
                'telefono', 'id_olimpiada', 'areas'
            ]);

            $result = $this->responsableService->createResponsable($responsableData);

            return response()->json([
                'message' => 'Responsable de área registrado exitosamente',
                'data' => $result
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al registrar responsable de área',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene todos los responsables de área.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $responsables = $this->responsableService->getAllResponsables();
            
            return response()->json([
                'message' => 'Responsables obtenidos exitosamente',
                'data' => $responsables
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener responsables',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene un responsable específico por ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $responsable = $this->responsableService->getResponsableById($id);
            
            if (!$responsable) {
                return response()->json([
                    'message' => 'Responsable no encontrado'
                ], 404);
            }

            return response()->json([
                'message' => 'Responsable obtenido exitosamente',
                'data' => $responsable
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener responsable',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
