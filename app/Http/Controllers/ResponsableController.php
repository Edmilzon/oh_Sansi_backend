<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
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

        // Validación personalizada para la combinación de área y olimpiada
        $request->validate([
            'areas.*' => [function ($attribute, $value, $fail) use ($request) {
                if (!DB::table('area_olimpiada')->where('id_area', $value)->where('id_olimpiada', $request->id_olimpiada)->exists()) {
                    $fail("El área con ID {$value} no está asociada a la olimpiada con ID {$request->id_olimpiada}.");
                }
            }],
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

    /**
     * Obtiene las gestiones en las que ha trabajado un responsable por su CI.
     *
     * @param string $ci
     * @return JsonResponse
     */
    public function getGestionesByCi(string $ci): JsonResponse
    {
        try {
            $gestiones = $this->responsableService->getGestionesByCi($ci);

            if (empty($gestiones)) {
                return response()->json([
                    'message' => 'No se encontraron gestiones para el responsable con el CI proporcionado o el usuario no es un responsable.',
                    'data' => []
                ]);
            }

            return response()->json($gestiones);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las gestiones del responsable.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene las áreas asignadas a un responsable para una gestión específica.
     *
     * @param string $ci
     * @param string $gestion
     * @return JsonResponse
     */
    public function getAreasByCiAndGestion(string $ci, string $gestion): JsonResponse
    {
        try {
            $areas = $this->responsableService->getAreasByCiAndGestion($ci, $gestion);

            if (empty($areas)) {
                return response()->json([
                    'message' => 'No se encontraron áreas asignadas para el responsable con el CI y la gestión proporcionados.',
                    'data' => []
                ]);
            }

            return response()->json($areas);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las áreas del responsable.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
