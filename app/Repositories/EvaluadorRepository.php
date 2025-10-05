<?php

namespace App\Repositories;

use App\Models\Evaluador;
use App\Models\Persona;
use App\Models\Usuario;

class EvaluadorRepository
{
    public function createPersona(array $data): Persona
    {
        return Persona::create($data);
    }

    public function createUsuario(array $data): Usuario
    {
        return Usuario::create($data);
    }

    public function createEvaluador(array $data): Evaluador
    {
        return Evaluador::create($data);
    }
}