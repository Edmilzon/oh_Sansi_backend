<?php

namespace App\Http\Controllers\Responsable;

use App\Services\ResponsableCompetidorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class CompetidorController extends Controller
{
    protected $responsableCompetidorService;

    public function __construct(ResponsableCompetidorService $responsableCompetidorService)
    {
        $this->responsableCompetidorService = $responsableCompetidorService;
    }

    /**
     * Muestra los competidores asociados a un responsable de área.
     *
     * @param int $id_persona
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(int $id_persona): JsonResponse
    {
        try {
            $competidores = $this->responsableCompetidorService->getCompetidoresPorResponsable($id_persona);

            return response()->json([
                'success' => true,
                'data' => $competidores,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los competidores del responsable.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}