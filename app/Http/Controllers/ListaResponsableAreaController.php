<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use App\Services\ListaResponsableAreaService;
use InvalidArgumentException;

class ListaResponsableAreaController extends Controller
{
    protected ListaResponsableAreaService $listaResponsableAreaService;

    public function __construct(ListaResponsableAreaService $listaResponsableAreaService)
    {
        $this->listaResponsableAreaService = $listaResponsableAreaService;
    }

    public function getNivelesPorArea(Request $request, $idArea): JsonResponse
    {
        $idArea = (int) $idArea;
        $niveles = $this->listaResponsableAreaService->getNivelesPorArea($idArea);

        return response()->json([
            'success' => true,
            'data' => ['niveles' => $niveles]
        ], 200);
    }

    public function getAreaPorResponsable(Request $request, $idResponsable): JsonResponse
    {
        $idResponsable = (int) $idResponsable;
        $areas = $this->listaResponsableAreaService->getAreaPorResponsable($idResponsable);

        return response()->json([
            'success' => true,
            'data' => ['areas' => $areas]
        ], 200);
    }

    public function listarPorAreaYNivel(Request $request, $idResponsable, $idArea, $idNivel, $grado): JsonResponse
    {
        $idResponsable = (int) $idResponsable;
        $idArea = (int) $idArea;
        $idNivel = (int) $idNivel;
        $grado = (int) $grado;

        try {
            $competidores = $this->listaResponsableAreaService->listarPorAreaYNivel(
                $idResponsable,
                $idArea,
                $idNivel,
                $grado
            );

            return response()->json([
                'success' => true,
                'data' => ['competidores' => $competidores]
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar competidores: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getGrado(): JsonResponse
    {
        try {
            $grados = $this->listaResponsableAreaService->getListaGrados();

            return response()->json([
                'success' => true,
                'data' => ['grados' => $grados]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los grados: ' . $e->getMessage()
            ], 500);
        }
    }
}
