<?php

namespace App\Repositories;

use App\Models\CodigoEvaluador;
use App\Models\Evaluador;
use App\Models\Persona;
use App\Models\Usuario;

class EvaluadorRepository
{
    public function findCodigo(string $codigo): ?CodigoEvaluador
    {
        return CodigoEvaluador::where('codigo', $codigo)->where('activo', true)->first();
    }

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

    public function loginEvaluador(string $email, string $password): ?Evaluador
    {
        return Evaluador::where('email', $email)->where('password', $password)->first();
    }
}