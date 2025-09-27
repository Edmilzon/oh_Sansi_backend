<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AreaService;
use Illuminate\Routing\Controller;

class AreaController extends Controller {

    protected $areaService;

    public function __construct(AreaService $areaService){
        $this-> areaService = $areaService;
    }
    public function index(){
    $areas = $this->areaService->getAreaList(); 
    return response()->json($areas); 
}
    
   
    public function store(Request $request){
    $validateData = $request->validate([
        'nombre'      => 'required|string',
        'descripcion' => 'nullable|string',
    ]);
    $area = $this->areaService->createNewArea($validateData);

    // Generar código
    $year = now()->format('Y'); // año actual
    $namePart = strtoupper(substr($area->nombre, 0, 3)); // primeras 3 letras en mayúsculas
    $codigo = $year . '-' . $namePart;

    $area->codigoEncargado()->create([
        'codigo' => $codigo,
        'descripcion' => 'Código encargado para ' . $area->nombre
    ]);

    return response()->json([
        'area' => $area,
        'codigo_encargado' => $area->codigoEncargado
    ], 201);
}
}