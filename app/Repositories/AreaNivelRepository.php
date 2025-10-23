<?php

namespace App\Repositories;

use App\Models\AreaNivel;
use App\Models\Area;
use Illuminate\Database\Eloquent\Collection;
class AreaNivelRepository{

    public function getAllAreasNiveles(): Collection{
        return AreaNivel::with(['areas', 'nivel','olimpiada'])->get();
    }

    public function getByArea(int $id_area, ?int $idOlimpiada = null): Collection
    {
    $query = AreaNivel::where('id_area', $id_area);
    
    if ($idOlimpiada) {
        $query->where('id_olimpiada', $idOlimpiada);
    }
    
    return $query->get();
    }
    
    public function getByAreaAll(int $id_area, ?int $idOlimpiada = null): Collection
    {
    $query = AreaNivel::with([
        'areas:id_area,nombre',
        'nivel:id_nivel,nombre',
        'olimpiada:id_olimpiada,gestion'
    ])
    ->where('id_area', $id_area);

    if ($idOlimpiada) {
        $query->where('id_olimpiada', $idOlimpiada);
    }
    return $query->get();
    }

    public function getAreaNivelAsignadosAll(): Collection
    {
        return Area::with([
            'areaNiveles' => function($query) use ($idOlimpiada) {
                $query->where('id_olimpiada', $idOlimpiada);
            },
            'areaNiveles.niveles:id_nivel,nombre,orden'
        ])
        ->where('activo', true)
        ->get(['id_area', 'nombre', 'activo']);
    }
    
    
    public function getById(int $id): ?AreaNivel
    {
        return AreaNivel::with(['areas', 'niveles','olimpiada'])->find($id);
    }

    public function createAreaNivel(array $data): AreaNivel
    {
    return AreaNivel::create($data);
    }

     public function update(int $id, array $data): bool
    {
        $areaNivel = AreaNivel::find($id);
        
        if (!$areaNivel) {
            return false;
        }

        return $areaNivel->update($data);
    }

    public function delete(int $id): bool
    {
        $areaNivel = AreaNivel::find($id);
        
        if (!$areaNivel) {
            return false;
        }

        return $areaNivel->delete();
    }
    
}