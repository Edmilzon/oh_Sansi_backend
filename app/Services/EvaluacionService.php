<?php

namespace App\Services;

use App\Repositories\EvaluacionRepository;
use App\Model\Examen;
use App\Model\Evaluacion;
use App\Events\CompetidorBloqueado;
use App\Events\CompetidorLiberado;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class EvaluacionService
{
    public function __construct(
        protected EvaluacionRepository $repo
    ) {}

    public function obtenerPizarraExamen(int $idExamen)
    {
        return Examen::with([
            'evaluaciones.competidor.persona',
            'evaluaciones.usuarioBloqueo.persona'
        ])->findOrFail($idExamen);
    }

    public function bloquearFicha(int $idEvaluacion, int $userId)
    {
        return DB::transaction(function () use ($idEvaluacion, $userId) {
            $existeJuez = DB::table('usuario')->where('id_usuario', $userId)->exists();
            if (!$existeJuez) {
                throw new Exception("El usuario identificador no es válido.", 401);
            }

            $evaluacion = $this->repo->findForUpdate($idEvaluacion);

            if ($evaluacion->examen->estado_ejecucion !== 'en_curso') {
                throw new Exception("El examen no está en curso, no se puede bloquear.");
            }

            if ($evaluacion->bloqueado_por && $evaluacion->bloqueado_por !== $userId) {
                $tiempoLimiteMinutos = 5;
                $horaBloqueo = Carbon::parse($evaluacion->fecha_bloqueo);

                if (now()->diffInMinutes($horaBloqueo) < $tiempoLimiteMinutos) {
                    $nombreJuez = $evaluacion->usuarioBloqueo->persona->nombre ?? 'otro juez';
                    throw new Exception("Ficha ocupada por {$nombreJuez}. Intente en unos instantes.", 409);
                }
            }

            $evaluacion = $this->repo->bloquear($evaluacion, $userId);

            $evaluacion->load('examen');
            broadcast(new CompetidorBloqueado($evaluacion))->toOthers();

            return $evaluacion;
        });
    }

    /**
     * Guardar Nota
     */
    public function guardarNota(int $idEvaluacion, array $datos)
    {
        return DB::transaction(function () use ($idEvaluacion, $datos) {
            $evaluacion = $this->repo->findForUpdate($idEvaluacion);

            if ($evaluacion->bloqueado_por !== $datos['user_id']) {
                throw new Exception("Perdiste el bloqueo de esta ficha.");
            }

            if ($evaluacion->esta_calificado && $evaluacion->nota != $datos['nota']) {
                if (empty($datos['motivo_cambio'])) throw new Exception("Motivo obligatorio al corregir.");

                $this->repo->registrarLog([
                    'id_evaluacion' => $idEvaluacion,
                    'id_usuario_autor' => $datos['user_id'],
                    'nota_anterior' => $evaluacion->nota,
                    'nota_nueva' => $datos['nota'],
                    'motivo_cambio' => $datos['motivo_cambio'],
                ]);
            }

            $evaluacion = $this->repo->updateNota($evaluacion, [
                'nota' => $datos['nota'],
                'estado_participacion' => $datos['estado_participacion'],
                'observacion' => $datos['observacion'] ?? null
            ]);

            $evaluacion->load('examen');
            broadcast(new CompetidorLiberado($evaluacion, $datos['nota']))->toOthers();

            return $evaluacion;
        });
    }

    /**
     * Descalificar
     */
    public function descalificarCompetidor(int $idEvaluacion, int $userId, string $motivo)
    {
        return DB::transaction(function () use ($idEvaluacion, $userId, $motivo) {
            $evaluacion = $this->repo->findForUpdate($idEvaluacion);

            if ($evaluacion->bloqueado_por !== $userId) {
                throw new Exception("Debes bloquear la ficha antes de descalificar.");
            }

            $this->repo->registrarLog([
                'id_evaluacion'    => $idEvaluacion,
                'id_usuario_autor' => $userId,
                'nota_anterior'    => $evaluacion->nota,
                'nota_nueva'       => 0,
                'motivo_cambio'    => "DESCALIFICACIÓN: $motivo",
            ]);

            $evaluacion = $this->repo->descalificar($evaluacion, $motivo);

            $evaluacion->load('examen');
            broadcast(new CompetidorLiberado($evaluacion, 0))->toOthers();

            return $evaluacion;
        });
    }

    public function desbloquearFicha(int $idEvaluacion, int $userId)
    {
        $evaluacion = $this->repo->find($idEvaluacion);

        if ($evaluacion->bloqueado_por !== null && $evaluacion->bloqueado_por !== $userId) {
            throw new Exception("No puedes desbloquear ficha ajena.");
        }

        $evaluacion = $this->repo->desbloquear($evaluacion);

        $evaluacion->load('examen');
        broadcast(new CompetidorLiberado($evaluacion))->toOthers();

        return $evaluacion;
    }

    public function listarAreasNivelesParaEvaluador(int $userId): array
    {
        $asignaciones = $this->repo->getAreasConExamenesPorEvaluador($userId);

        if ($asignaciones->isEmpty()) {
            return [];
        }

        $agrupado = $asignaciones->groupBy(function ($item) {
            return $item->areaNivel->areaOlimpiada->area->id_area;
        });

        $resultado = [];

        foreach ($agrupado as $idArea => $items) {
            $primero = $items->first();
            $nombreArea = $primero->areaNivel->areaOlimpiada->area->nombre;

            $niveles = $items->map(function ($asignacion) {
                return [
                    'id_area_nivel' => $asignacion->id_area_nivel,
                    'id_area_nivel_real' => $asignacion->areaNivel->id_area_nivel,
                    'nombre_nivel'  => $asignacion->areaNivel->nivel->nombre
                ];
            })->values()->toArray();

            $resultado[] = [
                'id_Area' => $idArea,
                'área'    => $nombreArea,
                'niveles' => $niveles
            ];
        }

        return $resultado;
    }
}
