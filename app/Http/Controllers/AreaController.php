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

    public function store(Request $request) {
       return DB::transaction(function() use ($request) {

       $validateData = $request->validate([
            'nombre' => 'required|string',
        ]);

        $existeArea = Area::where('nombre', $validateData['nombre'])->first();
        if ($existeArea) {
            return response()->json([
                'error' => 'El nombre del Área se encuentra registrado'
            ], 422);
        }

        $area = $this->areaService->createNewArea($validateData);

        return response()->json([
            'area' => $area,
            'message' => 'Área creada y asociada a la olimpiada actual'
        ], 201);
    });
    }
}