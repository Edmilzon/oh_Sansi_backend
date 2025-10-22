<?php

namespace App\Repositories;

use App\Models\Area;
use App\Models\Olimpiada;

class AreaRepository{

    public function getAllAreas(){
        return Area::all();
    }

    public function getAreasPorOlimpiada($idOlimpiada){
        return Area::deOlimpiada($idOlimpiada)->get();
    }

    public function createArea(array $data){
        return Area::create($data);
    }

    public function asociarAreaAOlimpiada($idArea, $idOlimpiada) {
        $area = Area::find($idArea);
        $area->olimpiadas()->attach($idOlimpiada);
        return $area;
    }
}