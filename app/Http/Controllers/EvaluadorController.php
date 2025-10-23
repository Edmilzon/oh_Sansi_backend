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

        return response()->json([
            'evaluador' => $evaluador
        ], 201);
    }
}