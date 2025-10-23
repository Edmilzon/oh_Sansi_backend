<?php

namespace App\Services;

use App\Repositories\AreaRepository;
use Illuminate\Support\Facades\Log;

class AreaService {
    protected $areaRepository;
    protected $olimpiadaService;

    public function __construct(AreaRepository $areaRepository, OlimpiadaService $olimpiadaService){
        $this->areaRepository = $areaRepository;
        $this->olimpiadaService = $olimpiadaService;
    }

    public function getAreaList(){
        return $this->areaRepository->getAllAreas();
    }

    public function getAreasPorGestion($gestion){

        $olimpiada = $this->olimpiadaService->obtenerOlimpiadaPorGestion($gestion);
        
        if (!$olimpiada) {
            return [
                'error' => true,
                'message' => "Error al obtener la olimpiada para la gestión '$gestion'"
            ];
        }
        
        $areas = $this->areaRepository->getAreasPorOlimpiada($olimpiada->id_olimpiada);
        
        return [
            'error' => false,
            'gestion' => $gestion,
            'areas' => $areas,
            'total_areas' => $areas->count()
        ];
    }

    public function createNewArea(array $data){
        $area = $this->areaRepository->createArea($data);
        
        $olimpiadaActual = $this->olimpiadaService->obtenerOlimpiadaActual();
        $this->areaRepository->asociarAreaAOlimpiada($area->id_area, $olimpiadaActual->id_olimpiada);
        
        return $area;
    }
}