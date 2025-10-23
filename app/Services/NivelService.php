<?php

namespace App\Services;

use App\Repositories\NivelRepository;

class NivelService {
    Protected $nivelRepository;

    public function __construct(NivelRepository $nivelRepository){
        $this->nivelRepository = $nivelRepository;
    }

    public function getNivelList(){
        return $this->nivelRepository->getAllNivel();
    }

    public function createNewNivel(array $data){
        return $this->nivelRepository->createNivel($data);
    }
}