<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Maneja la solicitud de inicio de sesión.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $result = $this->authService->login($credentials);

        if (!$result) {
            return response()->json([
                'message' => 'Las credenciales proporcionadas son incorrectas o el rol no está permitido.'
            ], 401);
        }

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'access_token' => $result['token'],
            'data' => $result['usuario'],
        ], 200);
    }
}