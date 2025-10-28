<?php

namespace App\Http\Controllers;

use App\Services\EvaluadorService;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class EvaluadorController extends Controller
{
    protected $evaluadorService;

    public function __construct(EvaluadorService $evaluadorService)
    {
        $this->evaluadorService = $evaluadorService;
    }

    /**
     * Registra un nuevo usuario evaluador.
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
        ], [
            'areas.*.exists' => 'Una o más de las áreas proporcionadas no son válidas.'
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

            $result = $this->evaluadorService->createEvaluador($responsableData);

            return response()->json([
                'message' => 'Evaluador registrado exitosamente',
                'data' => $result
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al registrar evaluador',
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
            $evaluadores = $this->evaluadorService->getAllEvaluadores();
            
            return response()->json([
                'message' => 'Evaluadores obtenidos exitosamente',
                'data' => $evaluadores
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener evaluadores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene un evaluador específico por ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $evaluador = $this->evaluadorService->getEvaluadorById($id);

            if (!$evaluador) {
                return response()->json([
                    'message' => 'Evaluador no encontrado'
                ], 404);
            }

            return response()->json([
                'message' => 'Evaluador obtenido exitosamente',
                'data' => $evaluador
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener evaluador',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza un evaluador existente por su CI.
     *
     * @param Request $request
     * @param string $ci
     * @return JsonResponse
     */
    public function updateByCi(Request $request, string $ci): JsonResponse
    {
        $usuario = DB::table('usuario')->where('ci', $ci)->first();

        if (!$usuario) {
            return response()->json(['message' => 'Evaluador no encontrado con el CI proporcionado.'], 404);
        }

        $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'apellido' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:usuario,email,' . $usuario->id_usuario . ',id_usuario',
            'password' => 'sometimes|required|string|min:8',
            'telefono' => 'nullable|string|max:20',
            'id_olimpiada' => 'sometimes|required|integer|exists:olimpiada,id_olimpiada',
            'areas' => 'sometimes|required|array|min:1',
            'areas.*' => ['integer', 'exists:area,id_area', function ($attribute, $value, $fail) use ($request) {
                if ($request->has('id_olimpiada') && !DB::table('area_olimpiada')->where('id_area', $value)->where('id_olimpiada', $request->id_olimpiada)->exists()) {
                    $fail("El área con ID {$value} no está asociada a la olimpiada con ID {$request->id_olimpiada}.");
                }
            }],
        ]);

        try {
            $data = $request->only([
                'nombre', 'apellido', 'email', 'password', 
                'telefono', 'id_olimpiada', 'areas'
            ]);

            $result = $this->evaluadorService->updateEvaluadorByCi($ci, $data);

            return response()->json([
                'message' => 'Evaluador actualizado exitosamente',
                'data' => $result
            ]);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el evaluador',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
