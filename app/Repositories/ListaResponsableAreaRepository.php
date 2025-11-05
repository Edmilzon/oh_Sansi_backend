<?php

namespace App\Repositories;

use App\Model\Area;
use App\Model\Nivel;
use App\Model\ResponsableArea;
use App\Model\Competidor;
use App\Model\Institucion;
use App\Model\GradoEscolaridad;
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
    
    public function getGradosEscolaridad(int $idNivel): Collection
    {
       return GradoEscolaridad ::where('area_nivel.id_nivel', $idNivel)
        ->join('grado_escolaridad', 'area_nivel.id_grado_escolaridad', '=', 'grado_escolaridad.id_grado_escolaridad')
        ->select('grado_escolaridad.id_grado_escolaridad', 'grado_escolaridad.nombre')
        ->distinct()
        ->orderBy('grado_escolaridad.id_grado_escolaridad')
        ->get();
    }

 public function ListarPorAreaYNivel(int $idResponsable, int $idArea, int $idNivel, int $idGrado): Collection
{
    $olimpiadaActual = DB::table('olimpiada')
        ->whereYear('created_at', now()->year)
        ->orderByDesc('id_olimpiada')
        ->first();

    if (!$olimpiadaActual) {
        return collect(); // No hay olimpiada activa
    }

    $areasDelResponsable = DB::table('responsable_area')
        ->join('area_olimpiada', 'responsable_area.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
        ->where('responsable_area.id_usuario', $idResponsable)
        ->where('area_olimpiada.id_olimpiada', $olimpiadaActual->id_olimpiada)
        ->pluck('area_olimpiada.id_area')
        ->unique()
        ->values();

    if ($areasDelResponsable->isEmpty()) {
        return collect();
    }

    $query = DB::table('competidor')
        ->join('persona', 'competidor.id_persona', '=', 'persona.id_persona')
        ->join('area_nivel', 'competidor.id_area_nivel', '=', 'area_nivel.id_area_nivel')
        ->join('area', 'area_nivel.id_area', '=', 'area.id_area')
        ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
        ->join('grado_escolaridad', 'competidor.id_grado_escolaridad', '=', 'grado_escolaridad.id_grado_escolaridad')
        ->join('institucion', 'competidor.id_institucion', '=', 'institucion.id_institucion')
        ->whereIn('area.id_area', $areasDelResponsable)
        ->where('area_nivel.id_olimpiada', $olimpiadaActual->id_olimpiada);

    if ($idArea !== 0)  { $query->where('area.id_area', $idArea); }
    if ($idNivel !== 0) { $query->where('nivel.id_nivel', $idNivel); }
    if ($idGrado !== 0) { $query->where('grado_escolaridad.id_grado_escolaridad', $idGrado); }

    $rows = $query->select(
            'persona.apellido as apellido',
            'persona.nombre as nombre',
            'persona.genero as genero',
            'persona.ci as ci',
            'competidor.departamento as departamento',
            'institucion.nombre as colegio',
            'area.nombre as area',
            'nivel.nombre as nivel',
            'grado_escolaridad.nombre as grado'
        )
        ->orderBy('persona.apellido')
        ->orderBy('persona.nombre')
        ->get();

    // Formato uniforme de salida
    return $rows->map(function ($r) {
        return [
            'apellido'    => $r->apellido,
            'nombre'      => $r->nombre,
            'genero'      => $r->genero,
            'departamento'=> $r->departamento,
            'colegio'     => $r->colegio,
            'ci'          => $r->ci,
            'area'        => $r->area,
            'nivel'       => $r->nivel,
            'grado'       => $r->grado,
        ];
    })->values();
}
}