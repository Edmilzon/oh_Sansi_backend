<?php

namespace App\Repositories;

use App\Model\Area;
use Illuminate\Support\Collection;

class AreaOlimpiadaRepository
{
    /**
     * Encuentra todas las áreas asociadas a una olimpiada específica.
     * Retorna solo el id y el nombre del área.
     *
     * @param int $idOlimpiada
     * @return Collection
     */
    public function findAreasByOlimpiadaId(int $idOlimpiada): Collection
    {
        return $this->findAreasBy('id_olimpiada', $idOlimpiada);
    }

    /**
     * Encuentra todas las áreas asociadas a una olimpiada por su gestión.
     *
     * @param string $gestion
     * @return Collection
     */
    public function findAreasByGestion(string $gestion): Collection
    {
        return $this->findAreasBy('gestion', $gestion);
    }

    private function findAreasBy(string $column, $value): Collection
    {
        return Area::whereHas('olimpiadas', fn($query) => $query->where("olimpiada.{$column}", $value))
            ->get(['id_area', 'nombre']);
    }
}
