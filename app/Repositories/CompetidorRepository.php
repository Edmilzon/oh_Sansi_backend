<?php

namespace App\Repositories;

use App\Models\Competidor;

class CompetidorRepository{

    public function createPersona(array $data): Persona
    {
        return Persona::create($data);
    }

    public function createCompetidor(array $data){
        return Competidor::create($data);
    }

     public function getAllCompetidores(){
        return Competidor::all();
    }
}