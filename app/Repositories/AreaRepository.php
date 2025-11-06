<?php

namespace App\Repositories;

use App\Model\Area;

class AreaRepository{

    public function getAllAreas(){
        return Area::all();
    }

    public function createArea(array $data){
        return Area::create($data);
    }
}