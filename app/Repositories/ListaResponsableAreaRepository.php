<?php

namespace App\Repositories;

use App\Model\Area;
use App\Model\Nivel;
use App\Model\ResponsableArea;
use App\Model\Competidor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ListaResponsableAreaRepository
{
    public function getNivelesByArea(int $idArea): Collection
    {
        $area = Area::find($idArea);

        if (! $area) {
            return collect();
        }

        return $area->niveles()
            ->select('nivel.id_nivel', 'nivel.nombre') 
            ->distinct()
            ->get()
            ->map(function($nivel) {
                return [
                'id_nivel' => $nivel->id_nivel,
                'nombre' => $nivel->nombre
                ];
             });

    }

    public function getAreaPorResponsable(int $idUsuario): Collection
    {
        return ResponsableArea::where('id_usuario', $idUsuario)
            ->join('area_olimpiada', 'responsable_area.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
            ->join('area', 'area_olimpiada.id_area', '=', 'area.id_area')
            ->select('area.id_area', 'area.nombre')
            ->distinct()
            ->get();
    }
   
 public function ListarPorAreaYNivel(int $idResponsable, int $idArea, int $idNivel): Collection
{
    // Áreas que administra el responsable
    $areasDelResponsable = DB::table('responsable_area')
        ->join('area_olimpiada', 'responsable_area.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
        ->where('responsable_area.id_usuario', $idResponsable)
        ->pluck('area_olimpiada.id_area')
        ->unique()
        ->values();

    if ($areasDelResponsable->isEmpty()) {
        return collect();
    }

    // Consulta principal: solo campos solicitados
    $query = DB::table('competidor')
        ->join('persona', 'competidor.id_persona', '=', 'persona.id_persona')
        ->join('area_nivel', 'competidor.id_area_nivel', '=', 'area_nivel.id_area_nivel')
        ->join('area', 'area_nivel.id_area', '=', 'area.id_area')
        ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
        ->whereIn('area.id_area', $areasDelResponsable);

    if ($idArea !== 0)  $query->where('area.id_area', $idArea);
    if ($idNivel !== 0) $query->where('nivel.id_nivel', $idNivel);

    $rows = $query->select(
            'persona.nombre as nombre',
            'persona.apellido as apellido',
            'area.nombre as area',
            'persona.ci as ci',
            'nivel.nombre as nivel',
            'competidor.grado_escolar as grado'
        )
        ->orderBy('persona.apellido')
        ->orderBy('persona.nombre')
        ->get();

    // Normalizar la salida exactamente con las claves solicitadas
    return $rows->map(function ($r) {
        return [
            'nombre' => $r->nombre,
            'apellido' => $r->apellido,
            'area' => $r->area,
            'ci' => $r->ci,
            'nivel' => $r->nivel,
            'grado' => $r->grado,
        ];
    })->values();
}
}