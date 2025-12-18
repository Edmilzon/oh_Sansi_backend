<?php

namespace App\Http\Controllers;

use App\Services\SistemaEstadoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class SistemaEstadoController extends Controller
{
    public function __construct(
        protected SistemaEstadoService $estadoService
    ) {}

    /**
     * Endpoint Maestro: GET /api/sistema/estado
     * Retorna la configuración temporal actual de la Olimpiada.
     */
    public function index(): JsonResponse
    {
        $snapshot = $this->estadoService->obtenerSnapshotDelSistema();
        return response()->json($snapshot);
    }
}
