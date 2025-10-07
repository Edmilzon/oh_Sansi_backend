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
        return $this->faseRepository->create($data);
    }


    public function obtenerTodasLasFases()
    {
        return $this->faseRepository->getAll();
    }
}