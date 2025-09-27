<?php

namespace App\Repositories;

use App\Models\Nivel;

class NivelRepository {

    public function getAllNivel(){
        return Nivel::all;
    }

    public function createNivel(array $data){
        return Nivel::create($data);
    }
}