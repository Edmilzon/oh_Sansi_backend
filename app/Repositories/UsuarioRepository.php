<?php

namespace App\Repositories;

use App\Model\Usuario;

class UsuarioRepository
{
    /**
     * Busca un usuario por su dirección de email.
     *
     * @param string $email
     * @return Usuario|null
     */
    public function findByEmail(string $email): ?Usuario
    {
        return Usuario::where('email', $email)
            ->with('roles') // Carga solo los roles del usuario.
            ->first();
    }
}
