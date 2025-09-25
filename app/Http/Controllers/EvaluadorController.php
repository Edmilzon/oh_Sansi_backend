<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEvaluadorRequest; // Asumiendo que crearás este archivo
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

    // Cambiar Request $request por el Form Request Object
    public function store(StoreEvaluadorRequest $request)
    {
        // La validación se realiza automáticamente por StoreEvaluadorRequest
        $validatedData = $request->validated();

        $evaluador = $this->evaluadorService->createNewEvaluador($validatedData); // Pasa los datos validados

        return response()->json(['evaluador' => $evaluador], 201);
    }
}