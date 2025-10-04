<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AreaNivelService;
use Illuminate\Routing\Controller;

class ProductController extends Controller {

    protected $areaNivelService;

    public function __construct(AreaNivelService $areaNivelService){
        $this->areaNivelService = $areaNivelService;
    }

    public function index(){
        $areaNiveles = $this->areaNivelService->getAreaNivelList();
        return response()->json($areaNiveles);
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
        'inserted' => $inserted
    ], 201);
}

}