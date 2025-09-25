<?php

namespace App\Http\Controllers;

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

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'ci' => 'required|string|unique:persona,ci',
            'fecha_nac' => 'required|date',
            'genero' => 'nullable|in:M,F',
            'telefono' => 'nullable|string|unique:persona,telefono',
            'email' => 'required|email|unique:persona,email',

            // Datos de Usuario
            'username' => 'required|string|unique:usuario,nombre',
            'password' => 'required|string|min:8|confirmed',

            // Código de acceso
            'codigo_evaluador' => 'required|string|exists:codigo_evaluador,codigo,activo,1',
        ]);

        $evaluador = $this->evaluadorService->createNewEvaluador($validatedData);

        return response()->json(['evaluador' => $evaluador], 201);
    }
}