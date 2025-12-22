<?php

namespace App\Services;

use App\Repositories\ReporteRepository;
use Exception;

class ReporteService
{
    public function __construct(
        protected ReporteRepository $repo
    ) {}
    
    public function generarHistorialPaginado(array $params): array
    {
        $page  = (int) $params['page'];
        $limit = (int) $params['limit'];

        $filtros = [
            'id_area'     => $params['id_area'] ?? null,
            'ids_niveles' => isset($params['ids_niveles']) ? explode(',', $params['ids_niveles']) : [],
            'search'      => $params['search'] ?? null,
        ];

        return $this->repo->getHistorialCompleto($page, $limit, $filtros);
    }

    public function obtenerAreasFiltro()
    {
        return $this->repo->getAreasActivas();
    }

    public function obtenerNivelesFiltro(int $idArea)
    {
        return $this->repo->getNivelesActivosPorArea($idArea);
    }

    public function obtenerResultadosOficiales(int $idCompetencia): array
    {
        $medallero = $this->repo->getMedallero($idCompetencia);

        if ($medallero->isNotEmpty()) {
            return [
                'tipo'   => 'MEDALLERO_FINAL',
                'titulo' => 'Medallero Oficial',
                'data'   => $medallero
            ];
        }

        $clasificados = $this->repo->getClasificados($idCompetencia);

        return [
            'tipo'   => 'LISTA_CLASIFICADOS',
            'titulo' => 'Nómina de Clasificados',
            'data'   => $clasificados
        ];
    }

    public function obtenerHistorialEvaluacion(int $idEvaluacion)
    {
        $logs = $this->repo->getLogCambios($idEvaluacion);

        if ($logs->isEmpty()) {
            return [
                'mensaje' => 'No existen cambios registrados para esta evaluación.',
                'data'    => []
            ];
        }

        return [
            'mensaje' => 'Historial recuperado correctamente.',
            'data'    => $logs
        ];
    }
}
