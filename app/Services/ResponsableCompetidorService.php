<?php

namespace App\Services;

use App\Models\Responsable;
use App\Repositories\CompetidorRepository;

class ResponsableCompetidorService
{
    protected $competidorRepository;

    public function __construct(CompetidorRepository $competidorRepository)
    {
        $this->competidorRepository = $competidorRepository;
    }

    /**
     * Obtiene la lista de competidores para un responsable de área específico.
     *
     * @param int $idPersona
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCompetidoresPorResponsable(int $idPersona)
    {
        // 1. Obtener los IDs de las áreas asignadas al responsable (persona)
        $areaIds = Responsable::where('id_persona', $idPersona)
                              ->where('activo', true)
                              ->pluck('id_area')
                              ->toArray();

        // 2. Obtener los competidores que pertenecen a esas áreas
        $competidores = $this->competidorRepository->getCompetidoresByAreaIds($areaIds);

        return $competidores;
    }
}
