<?php

namespace App\Services;

use App\Repositories\EvaluacionRepository;
use App\Model\Competencia;
use Illuminate\Support\Facades\DB;

class EvaluacionService
{
    protected $evaluacionRepository;

    public function __construct(EvaluacionRepository $evaluacionRepository)
    {
        $this->evaluacionRepository = $evaluacionRepository;
    }

    /**
     * Crea o actualiza una evaluación y actualiza el estado de la competencia.
     *
     * @param array $data Los datos para la evaluación.
     * @param int $id_competencia El ID de la competencia asociada.
     * @return \App\Model\Evaluacion
     */
    public function calificarCompetidor(array $data, int $id_competencia)
    {
        return DB::transaction(function () use ($data, $id_competencia) {
            $evaluacion = $this->evaluacionRepository->crearOActualizar($data);

            $competencia = Competencia::findOrFail($id_competencia);
            $competencia->id_evaluacion = $evaluacion->id_evaluacion;

            $competencia->estado = ($data['estado'] === false) ? 'Calificado' : 'En Calificacion';
            
            $competencia->save();

            return $evaluacion;
        });
    }
}