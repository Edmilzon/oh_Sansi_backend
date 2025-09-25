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
    $areas = $this->areaService->getAreaList(); // o getAreaList
    return response()->json($areas); // debe ser json, no view()
}
    
    
    public function store(Request $request){
        $validateData = $request->validate([
            'nombre'      => 'required|string',
            'descripcion' => 'nullable|string',
        ]);

        $area = $this->areaService->createNewArea($validateData);

        return response()->json([
            'area' => $area
        ], 201);
    }
}