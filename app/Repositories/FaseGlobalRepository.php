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
     * Trae todas las fases de la olimpiada con su cronograma anidado.
     * OPTIMIZADO con selects.
     */
    public function getByOlimpiada(int $idOlimpiada): Collection
    {
        return FaseGlobal::query()
            ->select('id_fase_global', 'id_olimpiada', 'codigo', 'nombre', 'orden')
            ->where('id_olimpiada', $idOlimpiada)
            ->with(['cronograma' => function ($q) {
                $q->select('id_cronograma_fase', 'id_fase_global', 'fecha_inicio', 'fecha_fin', 'estado');
            }])
            ->orderBy('orden', 'asc')
            ->get();
    }

    /**
     * Busca una fase específica y trae su cronograma adjunto.
     */
    public function find(int $id): FaseGlobal
    {
        return FaseGlobal::query()
            ->select('id_fase_global', 'id_olimpiada', 'codigo', 'nombre', 'orden')
            ->with(['cronograma' => function ($q) {
                $q->select('id_cronograma_fase', 'id_fase_global', 'fecha_inicio', 'fecha_fin', 'estado');
            }])
            ->where('id_fase_global', $id)
            ->firstOrFail();
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
            ->where('codigo', 'EVALUACION')
            ->whereHas('olimpiada', function ($q) {
                $q->where('estado', 1);
            })
            ->orderBy('orden', 'asc')
            ->get();
    }

}
