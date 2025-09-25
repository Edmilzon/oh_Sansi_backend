<?php

namespace App\Repositories;

use App\Models\Nivel;

class NivelRepository {

    public function getAllNivel(int $perPage = 15){
        return Nivel::paginate($perPage);
    }

    public function createNivel(array $data){
        return Nivel::create($data);
    }
}