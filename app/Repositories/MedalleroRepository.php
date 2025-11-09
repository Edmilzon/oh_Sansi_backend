<?php

namespace App\Repositories;

use App\Model\GradoEscolaridad;
use App\Model\Departamento;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MedalleroRepository
{
    public function getAreaPorResponsable(int $idResponsable): Collection
    {
        $gestionActual = date('Y');

        return DB::table('responsable_area')
        ->join('area_olimpiada', 'responsable_area.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
        ->join('area', 'area_olimpiada.id_area', '=', 'area.id_area')
        ->join('olimpiada', 'area_olimpiada.id_olimpiada', '=', 'olimpiada.id_olimpiada')
        ->select('area.id_area', 'area.nombre', 'olimpiada.gestion')
        ->where('responsable_area.id_usuario', $idResponsable)
        ->where('olimpiada.gestion', $gestionActual)
        ->distinct()
        ->orderBy('area.nombre')
        ->get();
    }
    public function getNivelesPorArea(int $idArea): Collection
{
    $gestionActual = date('Y'); // Año actual

    return DB::table('area_nivel')
        ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
        ->join('olimpiada', 'area_nivel.id_olimpiada', '=', 'olimpiada.id_olimpiada')
        ->select(
            'area_nivel.id_area_nivel',
            'nivel.id_nivel',
            'nivel.nombre as nombre_nivel',
            'olimpiada.gestion',
        )
        ->where('area_nivel.id_area', $idArea)
        ->where('olimpiada.gestion', $gestionActual)
        ->where('area_nivel.activo', true)
        ->orderBy('nivel.nombre')
        ->distinct()
        ->get();
}

}