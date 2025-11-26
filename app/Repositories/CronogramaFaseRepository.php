<?php

namespace App\Repositories;

use App\Model\CronogramaFase;
use Illuminate\Support\Carbon;

class CronogramaFaseRepository
{
    /**
     * Busca si existe una fase configurada y activa para la gestión dada.
     */
    public function buscarFaseActiva(int $idOlimpiada)
    {
        $ahora = Carbon::now();

        return CronogramaFase::with(['faseGlobal']) // Traemos el nombre de la fase
            ->where('id_olimpiada', $idOlimpiada)
            ->where('estado', 'En Curso')
            ->where('fecha_inicio', '<=', $ahora)
            ->where('fecha_fin', '>=', $ahora)
            ->first();
    }
}