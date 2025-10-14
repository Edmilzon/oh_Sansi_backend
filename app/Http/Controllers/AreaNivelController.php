<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAreaNivelRequest;
use App\Http\Requests\UpdateAreaNivelRequest;
use Illuminate\Http\Request;
use App\Services\AreaNivelService;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;

class AreaNivelController extends Controller {

    protected $areaNivelService;

    public function __construct(AreaNivelService $areaNivelService){
        $this->areaNivelService = $areaNivelService;
    }

    public function index(){
        
        try{
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
    
    public function store(Request $request){
    try {
        $validatedData = $request->validate([
            '*.id_area' => 'required|integer|exists:area,id_area',
            '*.id_nivel' => 'required|integer|exists:nivel,id_nivel', 
            '*.activo' => 'required|boolean'
        ]);

        \Log::info('=== INICIANDO STORE - DATOS VALIDADOS ===', $validatedData);
        
        $result = $this->areaNivelService->createMultipleAreaNivel($validatedData);
        
        \Log::info('=== STORE COMPLETADO ===', $result);
        
        return response()->json([
            'success' => true,
            'data' => $result['area_niveles'],
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

    public function getByArea($id_area){
    try{
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

    public function getByAreaAll($id_area){
        try{
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
                'message' => $result['message']
            ]);
        
        } catch (\Exception $e) {
            return response()->json([
            'success' => false,
            'message' => 'Error al obtener áreas con niveles: ' . $e->getMessage()
        ], 500);
        }
    }

    public function show($id){
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

    public function updateByArea($id_area, Request $request){
    try {
        $validatedData = $request->validate([
            '*.id_nivel' => 'required|integer|exists:nivel,id_nivel',
            '*.activo' => 'required|boolean'
        ]);

        $result = $this->areaNivelService->updateAreaNivelByArea($id_area, $validatedData);
        
        return response()->json([
            'success' => true,
            'data' => $result['area_niveles'],
            'message' => $result['message']
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar las relaciones área-nivel: ' . $e->getMessage()
        ], 400);
    }
    }

    public function update(UpdateAreaNivelRequest $request, $id){
        try {
            $result = $this->areaNivelService->updateAreaNivel($id, $request->validated());
            
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

    public function destroy($id){
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