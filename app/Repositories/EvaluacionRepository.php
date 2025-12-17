<?php

namespace App\Repositories;

use App\Model\Evaluacion;
use App\Model\LogCambioNota;
use App\Model\EvaluadorAn;
use Illuminate\Database\Eloquent\Collection;

class EvaluacionRepository
{
    /**
     * Busca una evaluación y aplica bloqueo pesimista (FOR UPDATE).
     * Evita que dos jueces editen al mismo tiempo.
     */
    public function findForUpdate(int $id): Evaluacion
    {
        return Evaluacion::lockForUpdate()->findOrFail($id);
    }

    /**
     * Busca sin bloqueo (lectura ligera).
     */
    public function find(int $id): Evaluacion
    {
        return Evaluacion::findOrFail($id);
    }

    /**
     * Semáforo Rojo: Marca la ficha como ocupada.
     */
    public function bloquear(Evaluacion $evaluacion, int $userId): Evaluacion
    {
        $evaluacion->update([
            'bloqueado_por' => $userId,
            'fecha_bloqueo' => now(),
        ]);

        return $evaluacion;
    }

    /**
     * Semáforo Verde: Libera la ficha sin guardar cambios.
     */
    public function desbloquear(Evaluacion $evaluacion): Evaluacion
    {
        $evaluacion->update([
            'bloqueado_por' => null,
            'fecha_bloqueo' => null,
        ]);

        return $evaluacion;
    }

    /**
     * Guardado estándar de nota (0-100).
     * Libera el bloqueo al terminar.
     */
    public function updateNota(Evaluacion $evaluacion, array $datos): Evaluacion
    {
        $evaluacion->update([
            'nota'                 => $datos['nota'],
            'estado_participacion' => $datos['estado_participacion'],
            'esta_calificado'      => true,
            'observacion'          => $datos['observacion'] ?? $evaluacion->observacion,
            // Limpieza automática
            'bloqueado_por'        => null,
            'fecha_bloqueo'        => null,
        ]);

        return $evaluacion;
    }

    /**
     * DESCALIFICACIÓN
     */
    public function descalificar(Evaluacion $evaluacion, string $motivo): Evaluacion
    {
        $evaluacion->update([
            'nota'                 => 0,
            'estado_participacion' => 'descalificado_etica',
            'resultado_calculado'  => 'DESCALIFICADO',
            'esta_calificado'      => true,
            'observacion'          => "Sanción: $motivo",
            'bloqueado_por'        => null,
            'fecha_bloqueo'        => null,
        ]);

        return $evaluacion;
    }

    /**
     * Registra cambios de notas o descalificaciones.
     */
    public function registrarLog(array $datos): LogCambioNota
    {
        return LogCambioNota::create([
            'id_evaluacion'    => $datos['id_evaluacion'],
            'id_usuario_autor' => $datos['id_usuario_autor'],
            'nota_anterior'    => $datos['nota_anterior'],
            'nota_nueva'       => $datos['nota_nueva'],
            'motivo_cambio'    => $datos['motivo_cambio'],
            'fecha_cambio'     => now(),
        ]);
    }

    /**
     * Obtiene las asignaciones de un evaluador que tengan EXÁMENES REALES creados.
     * Filtra por Olimpiada Actual.
     */
    public function getAreasConExamenesPorEvaluador(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return EvaluadorAn::query()
            ->where('id_usuario', $userId)
            ->where('estado', 1)

            ->whereHas('areaNivel.areaOlimpiada.olimpiada', function ($q) {
                $q->where('estado', 1);
            })

            ->whereHas('areaNivel.competencias', function ($qCompetencia) {
                $qCompetencia->whereHas('examenes');
            })
            ->with([
                'areaNivel.nivel',
                'areaNivel.areaOlimpiada.area'
            ])
            ->get();
    }
}
