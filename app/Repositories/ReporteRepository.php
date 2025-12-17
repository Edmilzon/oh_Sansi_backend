<?php

namespace App\Repositories;

use App\Model\Medallero;
use App\Model\Evaluacion;
use App\Model\LogCambioNota;
use Illuminate\Support\Facades\DB;

class ReporteRepository
{
    /**
     * Obtiene los ganadores desde la tabla definitiva 'medallero'.
     */
    public function getMedallero(int $idCompetencia)
    {
        return Medallero::where('id_competencia', $idCompetencia)
            ->with(['competidor.persona', 'competidor.institucion'])
            ->orderBy('puesto')
            ->get()
            ->map(function ($item) {
                return [
                    'puesto'      => $item->puesto,
                    'medalla'     => $item->medalla,
                    'nombre'      => $item->competidor->persona->nombre . ' ' . $item->competidor->persona->apellido,
                    'institucion' => $item->competidor->institucion->nombre,
                ];
            });
    }

    /**
     * Obtiene clasificados desde la tabla 'evaluacion' (usando el campo caché).
     */
    public function getClasificados(int $idCompetencia)
    {
        return Evaluacion::whereHas('examen', function ($q) use ($idCompetencia) {
                $q->where('id_competencia', $idCompetencia);
            })
            ->where('resultado_calculado', 'CLASIFICADO')
            ->with(['competidor.persona', 'competidor.institucion', 'examen'])
            ->get()
            ->map(function ($item) {
                return [
                    'examen'      => $item->examen->nombre,
                    'nombre'      => $item->competidor->persona->nombre . ' ' . $item->competidor->persona->apellido,
                    'institucion' => $item->competidor->institucion->nombre,
                    'nota'        => $item->nota,
                    'resultado'   => $item->resultado_calculado,
                ];
            });
    }

    /**
     * Obtiene el historial de auditoría de una nota.
     */
    public function getLogCambios(int $idEvaluacion)
    {
        return LogCambioNota::where('id_evaluacion', $idEvaluacion)
            ->with('autor.persona') // Para ver quién hizo el cambio
            ->orderByDesc('fecha_cambio')
            ->get()
            ->map(function ($log) {
                return [
                    'fecha'         => $log->fecha_cambio->format('d/m/Y H:i'),
                    'autor'         => $log->autor->persona->nombre . ' ' . $log->autor->persona->apellido,
                    'nota_anterior' => $log->nota_anterior,
                    'nota_nueva'    => $log->nota_nueva,
                    'motivo'        => $log->motivo_cambio,
                ];
            });
    }
}
