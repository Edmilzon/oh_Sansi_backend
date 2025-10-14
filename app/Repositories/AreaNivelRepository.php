<?php

namespace App\Repositories;

use App\Models\AreaNivel;
use App\Models\Area;
use Illuminate\Database\Eloquent\Collection;
class AreaNivelRepository{

    public function getAllAreasNiveles(): Collection{
        return AreaNivel::with(['area', 'nivel'])->get();
    }

    public function getByArea(int $id_area): Collection
    {
    return AreaNivel::where('id_area', $id_area)-> get();
    }
    
    public function getByAreaAll(int $id_area): Collection
    {
    return AreaNivel::with([
        'area:id_area,nombre,descripcion,activo',
        'nivel:id_nivel,nombre,descripcion,orden'
    ])
    ->where('id_area', $id_area)
    ->get();
    }

    public function getAreaNivelAsignadosAll(): Collection
    {
        return Area::with([
            'areaNiveles.nivel'
        ])
        ->get(['id_area', 'nombre', 'descripcion', 'activo']);
    }
    
    public function getById(int $id): ?AreaNivel
    {
        return AreaNivel::with(['area', 'nivel'])->find($id);
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