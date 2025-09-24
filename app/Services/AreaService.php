<?php

namespace App\Services;

use App\Repositories\AreaRepository;

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
}
