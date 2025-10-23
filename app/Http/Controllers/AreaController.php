<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use App\Services\AreaService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class AreaController extends Controller {

    protected $areaService;

    public function __construct(AreaService $areaService){
        $this->areaService = $areaService;
    }

    public function getAreasPorGestion($gestion)
    {
        $resultado = $this->areaService->getAreasPorGestion($gestion);

        if ($resultado['error']) {
            return response()->json([
                'error' => $resultado['message']
            ], 404);
        }
        
        return response()->json([
            'gestion' => $resultado['gestion'],
            'total_areas' => $resultado['total_areas'],
            'areas' => $resultado['areas']
        ]);
    }

    public function index(Request $request){
        if ($request->has('gestion')) {
            $resultado = $this->areaService->getAreasPorGestion($request->gestion);

            if ($resultado['error']) {
                return response()->json([
                    'error' => $resultado['message']
                ], 404);
            }
            
            return response()->json([
                'gestion' => $resultado['gestion'],
                'total_areas' => $resultado['total_areas'],
                'areas' => $resultado['areas']
            ]);
        } else {

            $areas = $this->areaService->getAreaList();
            return response()->json([
                'message' => 'Todas las áreas (sin filtro por gestión)',
                'total_areas' => $areas->count(),
                'areas' => $areas
            ]);
        }
    }

    public function store(Request $request){
    try {
        $validatedData = $request->validate([
            '*.id_area' => 'required|integer|exists:areas,id_area',
            '*.id_nivel' => 'required|integer|exists:niveles,id_nivel', 
            '*.activo' => 'required|boolean'
        ]);
        
        $result = $this->areaNivelService->createMultipleAreaNivel($validatedData);
        
        return response()->json([
            'success' => true,
            'data' => $result['area_niveles'],
            'olimpiada' => $result['olimpiada'],
            'message' => $result['message'],
            'count_created' => count($result['area_niveles'])
        ], 201);
        
    } catch (\Exception $e) {
        \Log::error('=== ERROR EN STORE ===', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error al crear las relaciones área-nivel: ' . $e->getMessage(),
            'debug_info' => 'Revisar logs para más detalles'
        ], 400);
    }
}
}