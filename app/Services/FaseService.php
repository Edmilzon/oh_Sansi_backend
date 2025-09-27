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

        $faseExistente = \App\Models\Fase::where('orden', $data['orden'])->first();
        if ($faseExistente) {
            throw ValidationException::withMessages([
                'orden' => 'Ya existe una fase con este orden.'
            ]);
        }

        return $this->faseRepository->create($data);
    }

    public function obtenerFase($idFase)
    {
        $fase = $this->faseRepository->getById($idFase);
        
        if (!$fase) {
            throw ValidationException::withMessages([
                'fase' => 'No se encontró la fase solicitada.'
            ]);
        }

        return $fase;
    }

    public function obtenerTodasLasFases()
    {
        return $this->faseRepository->getAll();
    }
}