<?php

namespace App\Services;

use App\Repositories\CompetenciaRepository;
use App\Model\Competencia;
use App\Model\Medallero;
use App\Model\ParametroMedallero;
use App\Events\CompetenciaFinalizada;
use App\Events\CompetenciaEstadoCambiado;
use Illuminate\Support\Facades\DB;
use Exception;

class CierreCompetenciaService
{
    public function __construct(
        protected CompetenciaRepository $repository
    ) {}

    public function concluirYCalcular(int $idCompetencia): Competencia
    {
        return DB::transaction(function () use ($idCompetencia) {

            $competencia = $this->repository->findWithFullHierarchy($idCompetencia);

            if ($competencia->estado_fase !== 'en_proceso') {
                throw new Exception("Solo se pueden concluir competencias que estén en estado 'en_proceso'. Estado actual: {$competencia->estado_fase}");
            }

            foreach ($competencia->examenes as $examen) {
                if ($examen->estado_ejecucion !== 'finalizada') {
                    throw new Exception("Bloqueo de seguridad: El examen '{$examen->nombre}' aún no ha finalizado. Cierre todas las mesas antes de continuar.");
                }
            }

            if ($competencia->criterio_clasificacion === 'suma_ponderada') {
                $this->procesarSumaPonderada($competencia);
            } else {
                throw new Exception("El criterio de clasificación '{$competencia->criterio_clasificacion}' no está implementado.");
            }

            $competencia->update(['estado_fase' => 'concluida']);

            broadcast(new CompetenciaFinalizada($competencia))->toOthers();
            broadcast(new CompetenciaEstadoCambiado($competencia, 'publicada'))->toOthers();

            return $competencia;
        });
    }

    private function procesarSumaPonderada(Competencia $competencia): void
    {
        $paramMedallero = ParametroMedallero::where('id_area_nivel', $competencia->id_area_nivel)->first();

        if (!$paramMedallero) {
            throw new Exception("Error de configuración: No se han definido los cupos de medallas (Tabla 'param_medallero') para este nivel.");
        }

        $idsCompetidores = collect();
        foreach ($competencia->examenes as $examen) {
            $idsCompetidores = $idsCompetidores->merge($examen->evaluaciones->pluck('id_competidor'));
        }
        $idsCompetidores = $idsCompetidores->unique();

        $ranking = collect();

        foreach ($idsCompetidores as $idCompetidor) {
            $notaFinalAcumulada = 0;

            foreach ($competencia->examenes as $examen) {
                $evaluacion = $examen->evaluaciones->where('id_competidor', $idCompetidor)->first();

                if (!$evaluacion || $evaluacion->estado_participacion !== 'presente') {
                    continue;
                }

                $nota = $evaluacion->nota;
                $maxima = $examen->maxima_nota;
                $ponderacion = $examen->ponderacion;

                if ($maxima > 0) {
                    $puntosGanados = ($nota / $maxima) * $ponderacion;
                    $notaFinalAcumulada += $puntosGanados;
                }
            }

            $ranking->push([
                'id_competidor' => $idCompetidor,
                'nota_final' => round($notaFinalAcumulada, 2)
            ]);
        }

        $rankingOrdenado = $ranking->sortByDesc('nota_final');

        Medallero::where('id_competencia', $competencia->id_competencia)->delete();

        $cupos = [
            'ORO'     => $paramMedallero->oro ?? 0,
            'PLATA'   => $paramMedallero->plata ?? 0,
            'BRONCE'  => $paramMedallero->bronce ?? 0,
            'MENCION' => $paramMedallero->mencion ?? 0,
        ];

        $puesto = 1;

        foreach ($rankingOrdenado as $item) {
            $medallaAsignada = null;

            if ($cupos['ORO'] > 0) {
                $medallaAsignada = 'ORO';
                $cupos['ORO']--;
            } elseif ($cupos['PLATA'] > 0) {
                $medallaAsignada = 'PLATA';
                $cupos['PLATA']--;
            } elseif ($cupos['BRONCE'] > 0) {
                $medallaAsignada = 'BRONCE';
                $cupos['BRONCE']--;
            } elseif ($cupos['MENCION'] > 0) {
                $medallaAsignada = 'MENCION';
                $cupos['MENCION']--;
            }

            if ($medallaAsignada) {
                Medallero::create([
                    'id_competidor'  => $item['id_competidor'],
                    'id_competencia' => $competencia->id_competencia,
                    'puesto'         => $puesto,
                    'medalla'        => $medallaAsignada
                ]);
            }

            $puesto++;
        }
    }

    public function avalar(int $idCompetencia, int $idUsuarioAval): Competencia
    {
        $competencia = $this->repository->find($idCompetencia);

        if ($competencia->estado_fase !== 'concluida') {
            throw new Exception("La competencia debe estar 'concluida' (con resultados calculados) para ser avalada.");
        }

        $competencia->update([
            'estado_fase' => 'avalada',
            'id_usuario_aval' => $idUsuarioAval,
            'fecha_aval' => now()
        ]);

        // Opcional: Aquí podrías disparar un evento de 'CertificadosDisponibles'

        broadcast(new CompetenciaEstadoCambiado($competencia, 'publicada'))->toOthers();

        return $competencia;
    }
}
