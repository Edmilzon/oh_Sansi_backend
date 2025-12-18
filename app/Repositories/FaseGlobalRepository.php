<?php

namespace App\Repositories;

use App\Model\FaseGlobal;
use Illuminate\Database\Eloquent\Collection;

class FaseGlobalRepository
{
    /**
     * Crea una nueva fase global estructural.
     */
    public function create(array $data): FaseGlobal
    {
        return FaseGlobal::create($data);
    }

    /**
     * Encuentra una fase por ID.
     */
    public function find(int $id): ?FaseGlobal
    {
        return FaseGlobal::find($id);
    }

    /**
     * Obtiene todas las fases de una olimpiada ordenadas.
     */
    public function getByOlimpiada(int $idOlimpiada): Collection
    {
        return FaseGlobal::where('id_olimpiada', $idOlimpiada)
            ->with('cronograma')
            ->orderBy('orden', 'asc')
            ->get();
    }

    /**
     * Busca si existe una fase con el mismo orden en la misma olimpiada.
     */
    public function existeOrden(int $idOlimpiada, int $orden): bool
    {
        return FaseGlobal::where('id_olimpiada', $idOlimpiada)
            ->where('orden', $orden)
            ->exists();
    }

    /**
     * Obtiene las fases con código 'CLASIF' de la gestión actual.
     */
    public function getClasificatoriasActuales(): Collection
    {
        return FaseGlobal::query()
            ->select('id_fase_global', 'id_olimpiada', 'codigo', 'nombre', 'orden')
            ->where('codigo', 'EVALUACION') // Filtro duro solicitado
            ->whereHas('olimpiada', function ($q) {
                $q->where('estado', 1); // Solo gestión actual
            })
            ->orderBy('orden', 'asc')
            ->get();
    }
}
