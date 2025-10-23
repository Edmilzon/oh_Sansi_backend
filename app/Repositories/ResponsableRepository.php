<?php

namespace App\Repositories;

use App\Models\Responsable;
use App\Models\Persona;
use App\Models\Usuario;
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

    public function createPersona(array $data): Persona
    {
        return Persona::create($data);
    }

    public function createUsuario(array $data): Usuario
    {
        return Usuario::create($data);
    }

    public function createResponsable(array $data): Responsable
    {
        return Responsable::create($data);
    }
}


