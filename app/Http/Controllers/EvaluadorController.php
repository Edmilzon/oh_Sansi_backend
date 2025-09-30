<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEvaluadorRequest; 
use App\Services\EvaluadorService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class EvaluadorController extends Controller
{
    protected $evaluadorService;

    public function __construct(EvaluadorService $evaluadorService)
    {
        $this->evaluadorService = $evaluadorService;
    }

    public function store(StoreEvaluadorRequest $request)
    {
        $validatedData = $request->validated();
        $evaluador = $this->evaluadorService->createNewEvaluador($validatedData);

        // Obtener el usuario y el código de evaluador
        $usuario = $evaluador->usuario;
        $codigoEvaluador = null;
        $area = null;
        $nivel = null;

        if ($usuario && $usuario->id_codigo_evaluador) {
            $codigoEvaluador = \App\Models\CodigoEvaluador::find($usuario->id_codigo_evaluador);
            if ($codigoEvaluador) {
                $area = \App\Models\Area::find($codigoEvaluador->id_area);
                $nivel = \App\Models\Nivel::find($codigoEvaluador->id_nivel);
            }
        }

        return response()->json([
            'evaluador' => $evaluador,
            'area' => $area ? $area->nombre : null,
            'nivel' => $nivel ? $nivel->nombre : null,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $evaluador = $this->evaluadorService->loginEvaluador($credentials);

        if (! $evaluador) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        $usuario = \App\Models\Usuario::where('id_persona', $evaluador->id_persona)->first();
        $codigoEvaluador = null;
        if ($usuario && $usuario->id_codigo_evaluador) {
            $codigoEvaluador = \App\Models\CodigoEvaluador::find($usuario->id_codigo_evaluador);
        }

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'evaluador' => $evaluador,
            'usuario' => $usuario,
            'codigo_evaluador' => $codigoEvaluador,
            'token' => $token
        ], 200);
    }
}