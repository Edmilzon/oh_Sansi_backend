<?php

namespace App\Services;

use App\Repositories\FaseRepository;
use Illuminate\Validation\ValidationException;

class FaseService
{
    protected $faseRepository;

    public function __construct(FaseRepository $faseRepository)
    {
        $this->faseRepository = $faseRepository;
    }

    public function crearFase(array $data)
    {
        foreach ($data['niveles'] as $id_nivel){
            $data['id_nivel'] = $id_nivel;
            $this->faseRepository->create($data);
        }
        return $data;
    }


    public function obtenerTodasLasFases()
    {
        return $this->faseRepository->getAll();
    }
}