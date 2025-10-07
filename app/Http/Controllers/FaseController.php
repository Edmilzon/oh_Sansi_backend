<?php

namespace App\Http\Controllers;

use App\Http\Requests\FaseRequest;
use App\Services\FaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FaseController extends Controller
{
    protected $faseService;

    public function __construct(FaseService $faseService)
    {
        $this->faseService = $faseService;
    }

    public function store(FaseRequest $request): JsonResponse
    {

        try {
            DB::beginTransaction();

            $fase = $this->faseService->crearFase($request->validated());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fase creada exitosamente',
                'data' => $fase
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear fase',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(): JsonResponse
    {
        try {
            $fases = $this->faseService->obtenerTodasLasFases();

            return response()->json([
                'success' => true,
                'data' => $fases
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener fases',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}