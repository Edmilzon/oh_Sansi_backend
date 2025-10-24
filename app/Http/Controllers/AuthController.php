<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $result = $this->authService->login($request->only('email', 'password'));
            return response()->json($result);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    /**
     * Cierra la sesión del usuario.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user());
            return response()->json(['message' => 'Sesión cerrada exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al cerrar sesión'], 500);
        }
    }

    /**
     * Busca un usuario por su CI.
     *
     * @param string $ci
     * @return JsonResponse
     */
    public function getUserByCi(string $ci): JsonResponse
    {
        try {
            $user = $this->authService->getUserByCi($ci);
            
            if (!$user) {
                return response()->json(['message' => 'Usuario no encontrado'], 404);
            }
            
            return response()->json([
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al buscar usuario'], 500);
        }
    }
}
