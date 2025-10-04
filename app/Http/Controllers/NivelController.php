<?php

namespace App\Http\Controllers;

use App\Services\NivelService;
use App\Models\Nivel;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class NivelController extends Controller {
    protected $nivelService;

    public function __construct(NivelService $nivelService){
        $this->nivelService = $nivelService;
    }
    
    public function index() {
        $nivel = $this->nivelService->getNivelList();
        return response()->json($nivel);
    }

   public function store(Request $request) {
    return DB::transaction(function() use ($request) {

        $validatedData = $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'nullable|string',
            'orden' => 'nullable|integer'
        ]);

        $existeNivel = Nivel::where('nombre', $validatedData['nombre'])->first();
        if ($existeNivel) {
            return response()->json([
                'error' => 'El nivel: '. $validatedData['nombre'].' ya existe.'
            ], 422);
        }

        // Crear el nivel
        $nivel = $this->nivelService->createNewNivel($validatedData);

        return response()->json([
            'nivel' => $nivel
        ], 201);
    });
}

}