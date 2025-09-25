<?php

namespace App\Repositories;

use App\Models\Responsable;
use Illuminate\Support\Facades\DB;

class ResponsableRepository {

    public function getAllResponsables(){
        return DB::table('responsable_area as ra')
            ->join('persona as p', 'ra.id_persona', '=', 'p.id_persona')
            ->join('area as a', 'ra.id_area', '=', 'a.id_area')
            ->select(
                'ra.id_responsable_area',
                'ra.id_area',
                'a.nombre as nombreArea',
                'p.id_persona as idPersona',
                'p.nombre as nombrePersona',
                'p.telefono',
                'p.email',
                'ra.fecha_asignacion',
                'ra.activo'
            )
            ->get();
    }

    public function createResponsable(array $data){
        return Responsable::create($data);
    }
}


