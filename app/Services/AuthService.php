<?php

namespace App\Services;

use App\Models\Usuario;
use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    /**
     * Intenta autenticar a un usuario y generar un token.
     *
     * @param array $credentials Las credenciales (email, password).
     * @return array|null Un array con el usuario y el token, o null si falla.
     */
    public function login(array $credentials): ?array
    {
        $usuario = $this->authRepository->findUserByEmail($credentials['email']);

        if (!$usuario || !Hash::check($credentials['password'], $usuario->password)) {
            return null; 
        }

        // Roles permitidos para este login
        $allowedRoles = [Usuario::ROL_ADMIN, Usuario::ROL_RESPONSABLE, Usuario::ROL_EVALUADOR];

        if (!in_array($usuario->rol, $allowedRoles)) {
            return null;
        }

        // Cargar datos adicionales según el rol
        if ($usuario->rol === Usuario::ROL_RESPONSABLE) {
            $usuario->load('persona.responsableArea.area');
        } elseif ($usuario->rol === Usuario::ROL_EVALUADOR) {
            // Carga la persona, el rol de evaluador y el código de evaluador con su área y nivel.
            $usuario->load(['persona.evaluador']);
        } else {
            $usuario->load('persona');
        }

        // Crear el token de autenticación
        $token = $usuario->createToken('auth_token')->plainTextToken;

        return [
            'usuario' => $usuario,
            'token' => $token,
        ];
    }
}