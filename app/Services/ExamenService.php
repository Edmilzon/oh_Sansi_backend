<?php

namespace App\Services;

use App\Model\Examen;
use App\Model\Competencia;
use App\Model\Evaluacion;
use App\Repositories\ExamenRepository;
use App\Repositories\CompetidorRepository;
use App\Services\CalculadoraResultadosService;
use App\Events\ExamenEstadoCambiado;
use Illuminate\Support\Facades\DB;
use Exception;

class ExamenService
{
    public function __construct(
        protected ExamenRepository $repository,
        protected CompetidorRepository $competidorRepo,
        protected CalculadoraResultadosService $calculadoraService
    ) {}

    public function crearExamen(array $data): Examen
    {
        return DB::transaction(function () use ($data) {
            $competencia = Competencia::findOrFail($data['id_competencia']);

            if ($competencia->estado_fase !== 'borrador') {
                throw new Exception("Solo se pueden agregar exámenes en etapa de borrador.");
            }

            $nuevaPonderacion = (float) $data['ponderacion'];

            $sumaActual = $this->repository->sumarPonderaciones($competencia->id_competencia);

            $disponible = 100.00 - $sumaActual;

            if ($nuevaPonderacion > ($disponible + 0.01)) {
                throw new Exception("Error: La ponderación supera el 100%. Actualmente tienes ocupado el {$sumaActual}%, por lo que solo sobra {$disponible}%.");
            }

            $examen = $this->repository->create($data);
            $this->generarFichasIniciales($examen, $competencia);

            return $examen;
        });
    }

    public function actualizarExamen(int $id, array $data): Examen
    {
        $examen = $this->repository->find($id);

        if ($examen->estado_ejecucion !== 'no_iniciada') {
            throw new Exception("No se puede editar un examen iniciado o finalizado.");
        }

        if (isset($data['ponderacion'])) {
            $nuevaPonderacion = (float) $data['ponderacion'];

            $sumaOtros = $this->repository->sumarPonderaciones($examen->id_competencia, $id);
            $disponible = 100.00 - $sumaOtros;

            if ($nuevaPonderacion > ($disponible + 0.01)) {
                throw new Exception("Error: La ponderación supera el 100%. Solo sobra {$disponible}% disponible para asignar a este examen.");
            }
        }

        $this->repository->update($data, $id);
        return $this->repository->find($id);
    }

    private function generarFichasIniciales(Examen $examen, Competencia $competencia): void
    {
        $competidores = $this->competidorRepo->getHabilitadosPorAreaNivel($competencia->id_area_nivel);

        if ($competidores->isEmpty()) {
            return;
        }

        $fichasParaInsertar = [];
        $now = now();

        foreach ($competidores as $competidor) {
            $fichasParaInsertar[] = [
                'id_competidor' => $competidor->id_competidor,
                'id_examen' => $examen->id_examen,
                'nota' => 0.00,
                'estado_participacion' => 'presente',
                'esta_calificado' => false,
                'bloqueado_por' => null,
                'fecha_bloqueo' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Evaluacion::insert($fichasParaInsertar);
    }

    public function iniciarExamen(int $id): Examen
    {
        $examen = $this->repository->find($id);

        $this->sincronizarCompetidoresFaltantes($id);

        if ($examen->evaluaciones()->count() === 0) {
                throw new Exception("No se puede iniciar el examen: No hay competidores habilitados inscritos.");
        }

        if ($examen->competencia->estado_fase !== 'en_proceso') {
            throw new Exception("La competencia debe estar 'en_proceso' para iniciar exámenes.");
        }

        if ($examen->estado_ejecucion !== 'no_iniciada') {
            throw new Exception("El examen ya fue iniciado.");
        }

        $this->repository->update([
            'estado_ejecucion' => 'en_curso',
            'fecha_inicio_real' => now(),
        ], $id);

        $examen->refresh();
        broadcast(new ExamenEstadoCambiado($examen, 'en_curso'))->toOthers();

        return $examen;
    }

    public function finalizarExamen(int $id): Examen
    {
        return DB::transaction(function () use ($id) {
            $examen = Examen::lockForUpdate()->findOrFail($id);

            if ($examen->estado_ejecucion !== 'en_curso') {
                throw new Exception("Solo se puede finalizar un examen 'en_curso'.");
            }

            Evaluacion::where('id_examen', $id)
                ->whereNotNull('bloqueado_por')
                ->update(['bloqueado_por' => null, 'fecha_bloqueo' => null]);

            $examen->update(['estado_ejecucion' => 'finalizada']);

            $this->calculadoraService->procesarResultados($examen);

            broadcast(new ExamenEstadoCambiado($examen, 'finalizada'))->toOthers();

            return $examen;
        });
    }

    public function eliminarExamen(int $id): void
    {
        $examen = $this->repository->find($id);

        if ($examen->competencia->estado_fase !== 'borrador') {
            throw new Exception("No se puede eliminar exámenes de una competencia publicada.");
        }

        $tieneNotas = $examen->evaluaciones()->where('nota', '>', 0)->exists();
        if ($tieneNotas) {
                throw new Exception("No se puede eliminar: ya existen registros de notas.");
        }

        $this->repository->delete($id);
    }

    public function sincronizarCompetidoresFaltantes(int $idExamen): int
    {
        $examen = $this->repository->find($idExamen);
        $competidoresHabilitados = $this->competidorRepo->getHabilitadosPorAreaNivel($examen->competencia->id_area_nivel);

        $idsConFicha = $examen->evaluaciones()->pluck('id_competidor')->toArray();
        $nuevos = 0;
        $fichas = [];
        $now = now();

        foreach ($competidoresHabilitados as $competidor) {
            if (!in_array($competidor->id_competidor, $idsConFicha)) {
                $fichas[] = [
                    'id_competidor' => $competidor->id_competidor,
                    'id_examen' => $examen->id_examen,
                    'nota' => 0.00,
                    'estado_participacion' => 'presente',
                    'esta_calificado' => false,
                    'bloqueado_por' => null,
                    'fecha_bloqueo' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $nuevos++;
            }
        }

        if (!empty($fichas)) {
            Evaluacion::insert($fichas);
        }

        return $nuevos;
    }

    public function listarPorAreaNivel(int $idAreaNivel)
    {
        // Aquí podrías validar si el area_nivel existe, pero el repo retornará vacío si no.
        return $this->repository->getByAreaNivel($idAreaNivel);
    }

    public function listarParaCombo(int $idAreaNivel): array
    {
        $examenes = $this->repository->getSimpleByAreaNivel($idAreaNivel);

        return $examenes->map(function ($examen) {
            return [
                'id_examen' => $examen->id_examen,
                'nombre_examen' => $examen->nombre
            ];
        })->toArray();
    }

    public function listarCompetidores(int $idExamen): array
    {
        $this->repository->find($idExamen);

        $evaluaciones = $this->repository->getCompetidoresDeExamen($idExamen);

        return $evaluaciones->map(function ($eval) {
            $persona = $eval->competidor->persona;
            $grado = $eval->competidor->gradoEscolaridad->nombre ?? 'Sin Grado';

            // Lógica del Estado Visual
            $estadoTexto = 'Sin calificar'; // Default
            if ($eval->esta_calificado) {
                $estadoTexto = 'Calificado';
            } elseif ($eval->bloqueado_por !== null) {
                $estadoTexto = 'Calificando'; // O "Bloqueado"
            }

            return [
                'id_evaluacion'        => $eval->id_evaluacion,
                'id_competidor'        => $eval->id_competidor,
                'ci'                   => $persona->ci,
                'nombre_completo'      => $persona->nombre . ' ' . $persona->apellido,
                'grado_escolaridad'    => $grado,
                'estado_evaluacion'    => $estadoTexto, // "Sin calificar" | "Calificando" | "Calificado"
                'nota_actual'          => $eval->nota,
                'es_bloqueado'         => $eval->bloqueado_por !== null,
                'bloqueado_por_mi'     => false
            ];
        })->toArray();
    }
}
