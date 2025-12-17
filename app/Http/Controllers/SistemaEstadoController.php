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
     * GET /api/sistema/estado
     */
    public function index(): JsonResponse
    {
        $snapshot = $this->estadoService->obtenerSnapshotDelSistema();

        return response()->json($snapshot);
    }
}
