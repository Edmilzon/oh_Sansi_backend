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
        // Find the usuario by username (nombre) and verify the hashed password.
        $usuario = \App\Models\Usuario::where('nombre', $email)->first();

        if (! $usuario) {
            return null;
        }

        if (! \Illuminate\Support\Facades\Hash::check($password, $usuario->password)) {
            return null;
        }

        // Return the Evaluador related to this usuario's persona, if any
        return Evaluador::where('id_persona', $usuario->id_persona)->first();
    }
}