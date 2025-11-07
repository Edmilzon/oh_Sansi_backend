<?php

namespace App\Services;

use App\Repositories\UsuarioRepository;
use Illuminate\Support\Facades\Hash;

class UsuarioService
{
    protected $usuarioRepository;

    public function __construct(UsuarioRepository $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * Autentica un usuario y genera un token de acceso.
     *
     * @param array $credentials
     * @return array|null
     */
    public function login(array $credentials): ?array
    {
        $usuario = $this->usuarioRepository->findByEmail($credentials['email']);

        if (!$usuario || !Hash::check($credentials['password'], $usuario->password)) {
            return null; // Credenciales inválidas
        }

        // Elimina tokens antiguos para mantener la tabla limpia
        $usuario->tokens()->delete();

        // Crea un nuevo token que expira en 1 hora (configurado en config/sanctum.php)
        $token = $usuario->createToken('auth_token')->plainTextToken;

        // Obtiene una lista simple de los nombres de los roles del usuario.
        $roles = $usuario->roles->pluck('nombre');

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'nombre' => $usuario->nombre,
                'apellido' => $usuario->apellido,
                'email' => $usuario->email,
                'roles' => $roles,
            ]
        ];
    }
}
