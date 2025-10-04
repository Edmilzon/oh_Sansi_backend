<?php

namespace App\Repositories;

use App\Models\AreaNivel;
class AreaNivelRepository{

    public function getAllAreasNiveles(){
        return AreaNivel::all();
    }

    public function createAreaNivel(array $data){
        return AreaNivel::create($data);
    }
}