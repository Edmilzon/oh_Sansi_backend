<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\Competencia\StoreCompetenciaRequest;
use App\Http\Requests\Competencia\UpdateCompetenciaRequest;
use App\Http\Requests\Competencia\AvalarCompetenciaRequest;
use App\Http\Requests\Competencia\ConcluirCompetenciaRequest;
use App\Services\CompetenciaService;
use App\Services\CierreCompetenciaService;
use App\Repositories\CompetenciaRepository;
use Illuminate\Http\JsonResponse;
use App\Events\CompetenciaCreada;
use App\Repositories\AreaRepository;
use App\Repositories\FaseGlobalRepository;
use Exception;

class CompetenciaController extends Controller
{
    public function __construct(
        protected CompetenciaService $service,
        protected CierreCompetenciaService $cierreService,
        protected CompetenciaRepository $repository,
        protected AreaRepository $areaRepo,
        protected FaseGlobalRepository $faseRepo
    ) {}

    /**
     * Listar todas las competencias.
     */
    public function index(): JsonResponse
    {
        return response()->json($this->repository->getAll());
    }

    /**
     * Crear una nueva competencia (Estado: Borrador).
     */
    public function store(StoreCompetenciaRequest $request): JsonResponse
    {
        try {
            $competencia = $this->service->crear($request->validated());
            broadcast(new CompetenciaCreada($competencia))->toOthers();
            return response()->json([
                'message' => 'Competencia creada exitosamente.',
                'data' => $competencia
            ], 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Ver detalles completos (incluye exámenes y estado).
     */
    public function show(int $id): JsonResponse
    {
        try {
            return response()->json($this->repository->findWithFullHierarchy($id));
        } catch (Exception $e) {
            return response()->json(['message' => 'Competencia no encontrada'], 404);
        }
    }

    /**
     * Editar configuración (Solo si está en Borrador).
     */
    public function update(UpdateCompetenciaRequest $request, int $id): JsonResponse
    {
        try {
            $competencia = $this->service->actualizar($id, $request->validated());
            return response()->json([
                'message' => 'Competencia actualizada correctamente.',
                'data' => $competencia
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Eliminar competencia (Solo si está en Borrador).
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->eliminar($id);
            return response()->json(['message' => 'Competencia eliminada correctamente.']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Publicar (Hacer visible en agenda).
     * Valida que existan exámenes y ponderaciones al 100%.
     */
    public function publicar(int $id): JsonResponse
    {
        try {
            $competencia = $this->service->publicar($id);
            return response()->json([
                'message' => 'Competencia publicada. Ahora es visible.',
                'data' => $competencia
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Iniciar (Activar operación).
     * Permite que los exámenes individuales puedan abrirse.
     */
    public function iniciar(int $id): JsonResponse
    {
        try {
            $competencia = $this->service->iniciar($id);
            return response()->json([
                'message' => 'Competencia iniciada (En Proceso).',
                'data' => $competencia
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Cerrar y Calcular (El Gran Final).
     * Calcula promedios ponderados y asigna medallas.
     */
    public function cerrar(ConcluirCompetenciaRequest $request, int $id): JsonResponse
    {
        try {
            $competencia = $this->cierreService->concluirYCalcular($id);

            return response()->json([
                'message' => 'Competencia concluida. Resultados calculados y medallas asignadas.',
                'data' => $competencia
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        }
    }

    /**
     * Avalar (Firma Digital).
     * Congela los resultados para siempre. Requiere contraseña.
     */
    public function avalar(AvalarCompetenciaRequest $request, int $id): JsonResponse
    {
        try {
            $userId = $request->user() ? $request->user()->id_usuario : $request->input('user_id_simulado');

            if (!$userId) {
                return response()->json(['error' => 'No se pudo identificar al usuario firmante.'], 401);
            }

            $competencia = $this->cierreService->avalar($id, $userId);

            return response()->json([
                'message' => 'Resultados avalados oficialmente.',
                'data' => $competencia
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        }
    }

    /**
     * Lista competencias filtradas por Responsable y Área.
     */
    public function indexPorResponsable(int $idResponsable, int $idArea): JsonResponse
    {
        try {
            $competencias = $this->repository->getByResponsableAndArea($idResponsable, $idArea);
            return response()->json($competencias);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al cargar competencias: ' . $e->getMessage()], 500);
        }
    }

    // Fases Clasificatorias Actuales
    public function fasesClasificatorias(): JsonResponse
    {
        $fases = $this->faseRepo->getClasificatoriasActuales();
        return response()->json($fases);
    }

    // Áreas del Responsable (Actuales)
    public function areasResponsable(int $idUsuario): JsonResponse
    {
        $areas = $this->areaRepo->getByResponsableActual($idUsuario);
        return response()->json($areas);
    }

    public function nivelesPorArea(int $idArea): JsonResponse
    {
        try {
            $niveles = $this->service->listarNivelesPorArea($idArea);
            return response()->json($niveles);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint para obtener Áreas y Niveles de competencias YA CREADAS.
     */
    public function areasNivelesCreados(int $idUsuario): JsonResponse
    {
        try {
            $data = $this->service->agruparAreasNivelesPorResponsable($idUsuario);
            return response()->json($data);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
