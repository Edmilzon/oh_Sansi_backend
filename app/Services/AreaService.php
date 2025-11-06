<?php

namespace App\Services;

use App\Model\Area;
use App\Model\Olimpiada;
use App\Repositories\AreaRepository;
use Illuminate\Support\Facades\Log;

class AreaService {
    protected $areaRepository;

    public function __construct(AreaRepository $areaRepository){
        $this->areaRepository = $areaRepository;
    }

    public function getAreaList(){
        return $this->areaRepository->getAllAreas();
    }

    public function createNewArea(array $data){
        return $this->areaRepository->createArea($data);
    }

     public function obtenerAreasGestionActual()
    {
        $olimpiadaActual = $this->olimpiadaService->obtenerOlimpiadaActual();
        
        return Area::whereHas('olimpiadas', function($query) use ($olimpiadaActual) {
            $query->where('id_olimpiada', $olimpiadaActual->id_olimpiada);
        })->get();
    }

    public function obtenerAreasPorGestion($gestion)
    {
        $olimpiada = $this->olimpiadaService->obtenerOlimpiadaPorGestion($gestion);
        
        return Area::whereHas('olimpiadas', function($query) use ($olimpiada) {
            $query->where('id_olimpiada', $olimpiada->id_olimpiada);
        })->get();
    }

    public function vincularAreaAGestionActual($idArea)
    {
        $olimpiadaActual = $this->olimpiadaService->obtenerOlimpiadaActual();
        $area = Area::findOrFail($idArea);
        
        if (!$area->olimpiadas()->where('id_olimpiada', $olimpiadaActual->id_olimpiada)->exists()) {
            $area->olimpiadas()->attach($olimpiadaActual->id_olimpiada);
        }
        
        return $area;
    }
}