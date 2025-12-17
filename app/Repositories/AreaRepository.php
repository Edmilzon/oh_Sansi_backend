<?php

namespace App\Repositories;

use App\Model\Area;
use Illuminate\Support\Collection;

class AreaRepository{

    public function getAllAreas(){
        return Area::all();
    }

    public function createArea(array $data){

        return Area::create($data);

    }

    public function getAreasByGestion(string $gestion)

    {

        return Area::whereHas('olimpiadas', function ($query) use ($gestion) {

            $query->where('gestion', $gestion);

        })->select('id_area', 'nombre')->get();

    }

    /**
     * Obtiene las áreas asignadas a un responsable en la Olimpiada ACTIVA.
     */
    public function getByResponsableActual(int $idUsuario): Collection
    {
        return Area::query()
            ->select('id_area', 'nombre')
            ->whereHas('areaOlimpiadas', function ($q) use ($idUsuario) {
                // 1. Filtro de Olimpiada Activa
                $q->whereHas('olimpiada', function ($qOlim) {
                    $qOlim->where('estado', 1);
                })
                // 2. Filtro de Responsable Asignado
                ->whereHas('responsableArea', function ($qResp) use ($idUsuario) {
                    $qResp->where('id_usuario', $idUsuario);
                });
            })
            ->get();
    }

}
