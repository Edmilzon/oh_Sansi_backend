<?php

namespace App\Services;

use App\Repositories\FaseRepository;
use Illuminate\Database\Eloquent\Collection;

class FaseService
{
    protected $faseRepository;

    public function __construct(FaseRepository $faseRepository)
    {
        $this->faseRepository = $faseRepository;
    }

    public function obtenerFasesPorAreaNivel(int $id_area_nivel): Collection
    {
        return $this->faseRepository->obtenerPorAreaNivel($id_area_nivel);
    }

    public function crearFaseConCompetencia(array $data)
    {
        return $this->faseRepository->crearConCompetencia($data);
    }

    public function obtenerFasePorId(int $id_fase)
    {
        return $this->faseRepository->obtenerPorId($id_fase);
    }

    public function actualizarFase(int $id_fase, array $data)
    {
        return $this->faseRepository->actualizar($id_fase, $data);
    }

    public function eliminarFase(int $id_fase)
    {
        return $this->faseRepository->eliminar($id_fase);
    }
}
