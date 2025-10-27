<?php

namespace App\Repositories;

use App\Model\Area;
use App\Model\ResponsableArea;
use App\Model\Competidor;
use Illuminate\Support\Collection;

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
   
     public function ListarPorAreaYNivel(int $idArea, int $idNivel): Collection
{
    return Competidor::join('area_nivel', 'competidor.id_area_nivel', '=', 'area_nivel.id_area_nivel')
        ->join('area', 'area_nivel.id_area', '=', 'area.id_area')
        ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
        ->where('area.id_area', $idArea)
        ->when($idNivel !== 0, function ($query) use ($idNivel) {
            $query->where('nivel.id_nivel', $idNivel);
        })
        ->select('competidor.datos', 'area.nombre as area', 'nivel.nombre as nivel')
        ->get()
        ->map(function ($c) {
            $datosRaw = $c->datos;

            $datos = json_decode($datosRaw, true);

            if (is_string($datos)) {
                $datos2 = json_decode($datos, true);
                if (is_array($datos2)) {
                    $datos = $datos2;
                }
            }

            $datos = is_array($datos) ? $datos : [];

            return [
                'nombre'   => $datos['nombre']   ?? null,
                'apellido' => $datos['apellido'] ?? null,
                'ci'       => $datos['ci']       ?? $datos['carnet'] ?? null,
                'grado'    => $datos['grado']    ?? null,
                'area'     => $c->area,
                'nivel'    => $c->nivel,
            ];
        });
}

}
