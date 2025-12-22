<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Model\AccionSistema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccionSistemaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $acciones = AccionSistema::select('id_accion_sistema', 'codigo', 'nombre', 'descripcion')
            ->orderBy('nombre', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $acciones,
            'count' => $acciones->count()
        ]);
    }
}
