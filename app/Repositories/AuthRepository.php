<?php

namespace App\Repositories;

use App\Models\Usuario;
use App\Models\Persona;

class AuthRepository
{
    /**
     * Busca un usuario por su email a través de la tabla persona.
     *
     * @param string $email
     * @return Usuario|null
     */
    public function findUserByEmail(string $email): ?Usuario
    {
        $persona = Persona::where('email', $email)->first();
        return $persona ? $persona->usuario : null;
    }
}