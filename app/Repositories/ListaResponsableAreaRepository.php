<?php

namespace App\Repositories;

use App\Model\Area;
use App\Model\ResponsableArea;
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
            ->select('nivel.id_nivel', 'nivel.nombre') // solo columnas del nivel
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

}
