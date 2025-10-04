<?php

namespace App\Services;

use App\Repositories\AreaNivelRepository;

class AreaNivelService {
    protected $areaNivelRepository;

    public function __construct(AreaNivelRepository $areaNivelRepository){
        $this->areaNivelRepository = $areaNivelRepository;
    }

    public function getAreaNivelList(){
        return $this->areaNivelRepository->getAllAreasNiveles();
    }

    public function createNewAreaNivel(array $data){
        return $this->areaNivelRepository->createAreaNivel($data);
    }
}