<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\StoreParametroRequest;
use App\Services\ParametroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Model\Olimpiada;
use App\Model\AreaNivel;
use Illuminate\Support\Collection;

class ParametroController extends Controller
{
    public function __construct(
        protected ParametroService $service
    ) {}

    public function index(): JsonResponse
    {
        try {
            $result = $this->service->getAllParametros();
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(StoreParametroRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated()['area_niveles'];
            
            $olimpiadasActivas = Olimpiada::where('estado', true)->get();
            
            if ($olimpiadasActivas->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay olimpiadas activas. No se pueden guardar parámetros.',
                ], 422);
            }
            
            $idsOlimpiadasActivas = $olimpiadasActivas->pluck('id_olimpiada')->toArray();
            
            $idsAreaNivel = collect($validatedData)->pluck('id_area_nivel')->toArray();
            
            $areaNiveles = AreaNivel::whereIn('id_area_nivel', $idsAreaNivel)
                ->with(['areaOlimpiada.olimpiada'])
                ->get();
            
            $idsInvalidos = [];
            foreach ($areaNiveles as $areaNivel) {
                if (!in_array($areaNivel->areaOlimpiada->olimpiada->id_olimpiada, $idsOlimpiadasActivas)) {
                    $idsInvalidos[] = $areaNivel->id_area_nivel;
                }
            }
            
            if (count($idsInvalidos) > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Algunos parámetros no pertenecen a una olimpiada activa.',
                    'ids_invalidos' => $idsInvalidos
                ], 422);
            }
            
            $parametrosGuardados = $this->service->guardarParametrosMasivos($validatedData);
            
            $data = collect($parametrosGuardados)->map(function($parametro) {
                return [
                    'id_parametro' => $parametro->id_parametro,
                    'id_area_nivel' => $parametro->id_area_nivel,
                    'nota_min_aprobacion' => $parametro->nota_min_aprobacion,
                    'cantidad_maxima' => $parametro->cantidad_maxima,
                    'area_nivel' => [
                        'area' => $parametro->areaNivel->areaOlimpiada->area->nombre ?? null,
                        'nivel' => $parametro->areaNivel->nivel->nombre ?? null
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Parámetros guardados exitosamente.',
                'data' => $data
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getByOlimpiada(int $idOlimpiada): JsonResponse
    {
        try {
            $result = $this->service->getParametrosPorOlimpiada($idOlimpiada);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getParametrosByAreaNiveles(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'nullable|string']);

        try {
            $idsInput = $request->input('ids', '');
            
            if (empty($idsInput)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'No se proporcionaron IDs de área-nivel'
                ]);
            }
            
            $ids = array_map('intval', explode(',', $idsInput));
            $ids = array_filter($ids, function($id) {
                return is_numeric($id) && $id > 0;
            });
            
            if (empty($ids)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Los IDs proporcionados no son válidos'
                ]);
            }
            
            $result = $this->service->getParametrosByAreaNiveles($ids);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getAllParametrosByGestiones(): JsonResponse
    {
        try {
            $result = $this->service->getAllParametrosByGestiones();
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getParametrosGestionActual(): JsonResponse
    {
        try {
            $olimpiadasActivas = Olimpiada::where('estado', true)->get();
            
            if ($olimpiadasActivas->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay olimpiadas activas en este momento.',
                    'data' => []
                ], 404);
            }
            
            $todosParametros = new Collection();
            foreach ($olimpiadasActivas as $olimpiada) {
                $parametros = $this->service->getParametrosPorOlimpiada($olimpiada->id_olimpiada);
                $todosParametros = $todosParametros->merge($parametros);
            }
            
            return response()->json([
                'success' => true,
                'data' => $todosParametros
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => [] 
            ], 500);
        }
    }
}