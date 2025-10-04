<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAreaNivelRequest;
use App\Http\Requests\UpdateAreaNivelRequest;
use Illuminate\Http\Request;
use App\Services\AreaNivelService;
use Illuminate\Routing\Controller;

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
    // Validar que 'area' y 'nivel' estén presentes
    $validatedData = $request->validate([
        'area'  => 'required',
        'nivel' => 'required',
    ]);

    // Convertir ambos a arrays si vienen como valores simples
    $areas = is_array($validatedData['area']) ? $validatedData['area'] : [$validatedData['area']];
    $niveles = is_array($validatedData['nivel']) ? $validatedData['nivel'] : [$validatedData['nivel']];

    $inserted = [];

    foreach ($areas as $area) {
        foreach ($niveles as $nivel) {
            // Evitar insertar duplicados
            $existing = \App\Models\AreaNivel::where('id_area', $area)
                        ->where('id_nivel', $nivel)
                        ->first();
            if (!$existing) {
                $inserted[] = \App\Models\AreaNivel::create([
                    'id_area' => $area,
                    'id_nivel' => $nivel,
                    'activo' => false,
                ]);
            }
        }
    }

    return response()->json([
        'success' => true,
        'data' => $inserted,
        'count' => count($inserted),
        'message' => count($inserted) . ' relaciones área-nivel insertadas exitosamente.',
        ], 201);
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