<?php

namespace App\Repositories;

use App\Models\Competidor;
use App\Models\Persona;

class CompetidorRepository{


    public function findWithRelations($id) {
        return Competidor::with(['persona', 'institucion'])->find($id);
    }
    
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

    /**
     * Obtiene todos los competidores que pertenecen a una lista de áreas.
     *
     * @param array $areaIds
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCompetidoresByAreaIds(array $areaIds)
    {
        return Competidor::with([
                'persona', 
                'institucion', 
                'area', 
                'nivel'
            ])->whereIn('id_area', $areaIds)->get();
    }
}