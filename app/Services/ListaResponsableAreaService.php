<?php

namespace App\Services;

use App\Repositories\ListaResponsableAreaRepository;
use Illuminate\Support\Collection;

class ListaResponsableAreaService
{
    protected $listaResponsableAreaRepository;

    public function __construct(ListaResponsableAreaRepository $listaResponsableAreaRepository)
    {
        $this->listaResponsableAreaRepository = $listaResponsableAreaRepository;
    }
    
    public function getNivelesPorArea(int $idArea): Collection
    {
        return $this->listaResponsableAreaRepository->getNivelesByArea($idArea);
    }

    public function getNivelesPorAreaYOlimpiada(int $idArea, int $idOlimpiada): Collection
    {
        return $this->listaResponsableAreaRepository->getNivelesByAreaAndOlimpiada($idArea, $idOlimpiada);
    }
    
    public function getAreaPorResponsable(int $idResponsable): Collection
    {
        return $this->listaResponsableAreaRepository->getAreaPorResponsable($idResponsable);
    }
   
   public function listarPorAreaYNivel($idResponsable, $idArea, $idNivel, $grado)
{
    $competidores = $this->listaResponsableAreaRepository->listarPorAreaYNivel((int)$idResponsable,(int)$idArea, (int)$idNivel, (int)$grado);
    return response()->json($competidores);
}
}
