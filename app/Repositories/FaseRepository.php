<?php

namespace App\Repositories;

use App\Models\Fase;

class FaseRepository
{
    public function create(array $data)
    {
        return Fase::create($data);
    }

    public function getById($idFase)
    {
        return Fase::find($idFase);
    }

    public function getAll()
    {
        return Fase::orderBy('orden')->get();
    }
}