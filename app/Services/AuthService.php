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

    public function login(array $credentials): ?array
    {
        $usuario = $this->authRepository->findUserByEmail($credentials['email']);

        if (!$usuario || !Hash::check($credentials['password'], $usuario->password)) {
            return null; 
        }
        $usuario->load('roles');
        
        $rolesPermitidos = ['Administrador', 'Responsable Area', 'Evaluador'];
        if (!$usuario->roles->pluck('nombre')->intersect($rolesPermitidos)->count()) {
            return null;
        }

        $usuario->load(['responsableArea.areaNivel.area', 'evaluadorAn.areaNivel.area']);

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return [
            'usuario' => $usuario,
            'token' => $token,
        ];
    }
}