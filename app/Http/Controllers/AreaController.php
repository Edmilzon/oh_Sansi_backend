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
        $this-> areaService = $areaService;
    }
    public function index(){
    $areas = $this->areaService->getAreaList(); 
    return response()->json($areas); 
}

    public function store(Request $request) {
       return DB::transaction(function() use ($request) {

       $validateData = $request->validate([
            'nombre'      => 'required|string',
            'descripcion' => 'nullable|string',
        ]);

        
        $existeArea = Area::where('nombre', $validateData['nombre'])->first();
        if ($existeArea) {
            return response()->json([
                'error' => 'El nombre del Área se encuentra registrado'
            ], 422);
        }

        $area = $this->areaService->createNewArea($validateData);

        return response()->json([
            'area' => $area
        ], 201);
    });
}

}