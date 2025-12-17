<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\Evaluador\StoreEvaluadorRequest;
use App\Services\EvaluadorService;
use App\Model\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Exception;

class EvaluadorController extends Controller
{
    public function __construct(
        protected EvaluadorService $service
    ) {}

    /**
     * GET /api/evaluadores/dashboard
     * Pantalla principal del Juez: Muestra solo lo activo AHORA.
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $userId = $request->input('user_id') ?? ($request->user() ? $request->user()->id_usuario : null);

            if (!$userId) {
                return response()->json(['error' => 'Usuario no identificado. Se requiere user_id.'], 401);
            }

            $data = $this->service->obtenerDashboard($userId);

            return response()->json([
                'message' => 'Dashboard cargado exitosamente.',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Usuario::query();

            $query->join('persona', 'usuario.id_persona', '=', 'persona.id_persona')
                    ->select(
                        'usuario.*',
                        'persona.nombre',
                        'persona.apellido',
                        'persona.ci',
                        'persona.telefono'
                    );

            $query->whereHas('roles', function($q) {
                $q->where('nombre', 'Evaluador');
            });

            if ($search = $request->input('search')) {
                $query->where(function($q) use ($search) {
                    $q->where('persona.nombre', 'like', "%{$search}%")
                        ->orWhere('persona.apellido', 'like', "%{$search}%")
                        ->orWhere('persona.ci', 'like', "%{$search}%")
                        ->orWhere('usuario.email', 'like', "%{$search}%");
                });
            }

            if ($olimpiadaId = $request->input('olimpiada_id')) {
                $query->whereHas('roles', function($q) use ($olimpiadaId) {
                    $q->where('usuario_rol.id_olimpiada', $olimpiadaId);
                });
            }

            $evaluadores = $query->paginate($request->input('per_page', 10));

            return response()->json($evaluadores);

        } catch (\Exception $e) {
            Log::error("Error listando evaluadores: " . $e->getMessage());
            return response()->json(['message' => 'Error al listar evaluadores'], 500);
        }
    }

    public function store(StoreEvaluadorRequest $request): JsonResponse
    {
        try {
            $evaluador = $this->service->createEvaluador($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Evaluador registrado correctamente.',
                'data'    => $evaluador
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            Log::error("Error creando evaluador: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el evaluador.',
                'error'   => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function addAsignaciones(Request $request, string $ci): JsonResponse
    {
        try {
            $request->validate([
                'id_olimpiada'   => 'required|integer|exists:olimpiada,id_olimpiada',
                'area_nivel_ids' => 'required|array|min:1',
                'area_nivel_ids.*' => 'integer|exists:area_nivel,id_area_nivel'
            ]);

            $resultado = $this->service->addAsignacionesToEvaluador(
                $ci,
                $request->input('id_olimpiada'),
                $request->input('area_nivel_ids')
            );

            return response()->json([
                'success' => true,
                'message' => 'Asignaciones agregadas correctamente.',
                'data'    => $resultado
            ]);

        } catch (\Exception $e) {
            Log::error("Error agregando asignaciones a CI $ci: " . $e->getMessage());
            $status = str_contains($e->getMessage(), 'no existe') ? 404 : 500;

            return response()->json([
                'success' => false,
                'message' => 'No se pudieron agregar las asignaciones.',
                'error'   => $e->getMessage()
            ], $status);
        }
    }

    public function getAreasNivelesById($id): JsonResponse
    {
        try {
            $evaluador = $this->service->getEvaluadorById($id);

            if (!$evaluador) {
                return response()->json([
                    'success' => false,
                    'message' => 'Evaluador no encontrado.'
                ], Response::HTTP_NOT_FOUND);
            }

            $areasAsignadas = $evaluador['areas_asignadas'] ?? [];

            return response()->json([
                'success' => true,
                'message' => 'Áreas y niveles del evaluador obtenidos exitosamente.',
                'data'    => $areasAsignadas
            ]);

        } catch (\Exception $e) {
            Log::error("Error obteniendo áreas y niveles para evaluador ID $id: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las áreas y niveles del evaluador.',
                'error'   => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAsignacionesAgrupadas(int $id): JsonResponse
    {
        try {
            $data = $this->service->obtenerAreasNivelesAgrupados($id);
            return response()->json($data);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
