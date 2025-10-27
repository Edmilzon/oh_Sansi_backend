<?php

namespace App\Repositories;

use App\Model\Parametro;
use Illuminate\Database\Eloquent\Collection;

class ParametroRepository
{
    public function getAll(): Collection
    {
        return Parametro::with(['areaNivel', 'areaNivel.area', 'areaNivel.nivel', 'areaNivel.olimpiada'])
            ->get();
    }

    public function getByAreaNivel(int $idAreaNivel): ?Parametro
    {
        return Parametro::with(['areaNivel', 'areaNivel.area', 'areaNivel.nivel', 'areaNivel.olimpiada'])
            ->where('id_area_nivel', $idAreaNivel)
            ->first();
    }

    public function getByAreaNiveles(array $idsAreaNivel): Collection
    {
        return Parametro::with(['areaNivel', 'areaNivel.area', 'areaNivel.nivel', 'areaNivel.olimpiada'])
            ->whereIn('id_area_nivel', $idsAreaNivel)
            ->get();
    }

    public function getByOlimpiada(int $idOlimpiada): Collection
    {
        return Parametro::with(['areaNivel', 'areaNivel.area', 'areaNivel.nivel', 'areaNivel.olimpiada'])
            ->whereHas('areaNivel', function($query) use ($idOlimpiada) {
                $query->where('id_olimpiada', $idOlimpiada);
            })
            ->get();
    }

    public function create(array $data): Parametro
    {
        return Parametro::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $parametro = Parametro::find($id);
        
        if (!$parametro) {
            return false;
        }

        return $parametro->update($data);
    }

    public function updateOrCreateByAreaNivel(int $idAreaNivel, array $data): Parametro
    {
        return Parametro::updateOrCreate(
            ['id_area_nivel' => $idAreaNivel],
            $data
        );
    }

    public function delete(int $id): bool
    {
        $parametro = Parametro::find($id);
        
        if (!$parametro) {
            return false;
        }

        return $parametro->delete();
    }

    public function bulkCreateOrUpdate(array $parametrosData): array
    {
        $results = [];
        
        foreach ($parametrosData as $data) {
            $results[] = $this->updateOrCreateByAreaNivel(
                $data['id_area_nivel'], 
                $data
            );
        }
        
        return $results;
    }

    public function getAllParametrosByGestiones(): Collection
    {
    return Parametro::join('area_nivel', 'parametro.id_area_nivel', '=', 'area_nivel.id_area_nivel')
        ->join('area', 'area_nivel.id_area', '=', 'area.id_area')
        ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
        ->join('olimpiada', 'area_nivel.id_olimpiada', '=', 'olimpiada.id_olimpiada')
        ->select([
            'olimpiada.id_olimpiada',
            'olimpiada.gestion',
            'area_nivel.id_area_nivel',
            'area.nombre as nombre_area',
            'nivel.nombre as nombre_nivel',
            'parametro.nota_min_clasif as nota_minima',
            'parametro.nota_max_clasif as nota_maxima',
            'parametro.cantidad_max_apro as cant_max_clasificados'
        ])
        ->orderBy('olimpiada.gestion', 'desc')
        ->orderBy('area.nombre')
        ->orderBy('nivel.nombre')
        ->get();
    }

    public function getParametrosByAreaNiveles(array $idsAreaNivel): Collection
{
    return Parametro::join('area_nivel', 'parametro.id_area_nivel', '=', 'area_nivel.id_area_nivel')
        ->join('area', 'area_nivel.id_area', '=', 'area.id_area')
        ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
        ->join('olimpiada', 'area_nivel.id_olimpiada', '=', 'olimpiada.id_olimpiada')
        ->whereIn('parametro.id_area_nivel', $idsAreaNivel)
        ->select([
            'olimpiada.id_olimpiada',
            'olimpiada.gestion',
            'area_nivel.id_area_nivel',
            'area.nombre as nombre_area',
            'nivel.nombre as nombre_nivel',
            'parametro.nota_min_clasif as nota_minima',
            'parametro.nota_max_clasif as nota_maxima',
            'parametro.cantidad_max_apro as cant_max_clasificados'
        ])
        ->orderBy('area_nivel.id_area_nivel')
        ->orderBy('olimpiada.gestion', 'desc')
        ->get();
}
}