<?php

namespace App\Services;

use App\Repositories\ReporteRepository;
use App\Model\Competencia;
use Exception;

class ReporteService
{
    public function __construct(
        protected ReporteRepository $repository
    ) {}

    public function obtenerResultadosOficiales(int $idCompetencia): array
    {
        $competencia = Competencia::with('faseGlobal')->findOrFail($idCompetencia);

        if (in_array($competencia->estado_fase, ['borrador', 'publicada'])) {
            throw new Exception("Los resultados aún no están disponibles.");
        }

        if ($competencia->faseGlobal->codigo === 'FINAL') {
            return [
                'titulo' => 'Medallero Oficial',
                'estado' => $competencia->estado_fase,
                'data'   => $this->repository->getMedallero($idCompetencia)
            ];
        } else {
            return [
                'titulo' => 'Lista de Clasificados',
                'estado' => $competencia->estado_fase,
                'data'   => $this->repository->getClasificados($idCompetencia)
            ];
        }
    }

    public function obtenerHistorialEvaluacion(int $idEvaluacion)
    {
        return $this->repository->getLogCambios($idEvaluacion);
    }
}
