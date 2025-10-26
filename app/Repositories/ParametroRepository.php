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
}