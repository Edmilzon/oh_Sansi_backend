<?php

namespace App\Http\Controllers;

use App\Services\NivelService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class NivelController extends Controller {
    protected $nivelService;

    public function __construct(NivelService $nivelService){
        $this->nivelService = $nivelService;
    }
    
    public function index() {
        $nivel = $this->nivelService->getNivelList();
        return response()->json($nivel);
    }

    public function store (Request $request) {
        $validateData = $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'nullable|string',
            'orden' => 'required|integer'
        ]);

        $nivel = $this->nivelService->createNewNivel($validateData);
        return response()->json([
            'nivel' => $nivel
        ],201);
    }
}