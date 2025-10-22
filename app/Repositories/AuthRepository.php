<?php

namespace App\Repositories;

use App\Models\Usuario;

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
        return Usuario::where('email', $email)->first();
    }
}