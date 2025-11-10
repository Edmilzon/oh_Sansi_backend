<?php

namespace App\Repositories;

use App\Model\Evaluacion;
use Illuminate\Database\Eloquent\Model;

class EvaluacionRepository
{
    /**
     * Crea o actualiza una evaluación.
     *
     * @param array $data Los datos para la evaluación.
     * @param int|null $id_evaluacion El ID de la evaluación a actualizar, o null para crear.
     * @return Model
     */
    public function crearOActualizar(array $data, ?int $id_evaluacion = null): Model
    {
        return Evaluacion::updateOrCreate(
            ['id_evaluacion' => $id_evaluacion],
            $data
        );
    }
}
