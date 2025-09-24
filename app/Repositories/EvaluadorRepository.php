<?php

namespace App\Repositories;

use App\Models\CodigoEvaluador;
use App\Models\Evaluador;
use App\Models\Persona;
use App\Models\Usuario;

class EvaluadorRepository
{
    public function findCodigo(string $codigo)
    {
        return CodigoEvaluador::where('codigo', $codigo)->where('activo', true)->first();
    }

    public function createPersona(array $data)
    {
        return Persona::create($data);
    }

    public function createUsuario(array $data)
    {
        return Usuario::create($data);
    }

    public function createEvaluador(array $data)
    {
        return Evaluador::create($data);
    }
}