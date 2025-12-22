<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Model\DescalificacionAdministrativa;
use App\Model\Evaluacion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DescalificacionController extends Controller
{
    /**
     * Obtener la lista consolidada de todos los descalificados.
     * Incluye:
     * 1. Descalificaciones Administrativas (Expulsión del evento).
     * 2. Descalificaciones por Ética en Examen (Copia/Fraude en sala).
     */
    public function index(): JsonResponse
    {
        // 1. Obtener Administrativas
        $administrativas = DescalificacionAdministrativa::with(['competidor.persona', 'competidor.institucion'])
            ->get()
            ->map(function ($item) {
                $persona = $item->competidor->persona ?? null;
                return [
                    'tipo'            => 'ADMINISTRATIVA',
                    'id_competidor'   => $item->id_competidor,
                    'nombre_completo' => $persona ? ($persona->nombre . ' ' . $persona->apellido) : 'Desconocido',
                    'ci'              => $persona->ci ?? null,
                    'institucion'     => $item->competidor->institucion->nombre ?? 'Sin Institución',
                    'motivo'          => $item->observaciones,
                    'fecha'           => $item->fecha_descalificacion,
                    'examen_afectado' => 'TODO EL EVENTO', // Aplica a nivel global
                ];
            });

        // 2. Obtener Éticas (Examen)
        $eticas = Evaluacion::where('estado_participacion', 'descalificado_etica')
            ->with(['competidor.persona', 'competidor.institucion', 'examen'])
            ->get()
            ->map(function ($item) {
                $persona = $item->competidor->persona ?? null;
                return [
                    'tipo'            => 'ETICA_EXAMEN',
                    'id_competidor'   => $item->id_competidor,
                    'nombre_completo' => $persona ? ($persona->nombre . ' ' . $persona->apellido) : 'Desconocido',
                    'ci'              => $persona->ci ?? null,
                    'institucion'     => $item->competidor->institucion->nombre ?? 'Sin Institución',
                    'motivo'          => $item->observacion ?? 'Sin observación',
                    'fecha'           => $item->updated_at, // Fecha de la sanción
                    'examen_afectado' => $item->examen->nombre ?? 'Examen desconocido',
                ];
            });

        // 3. Unir y ordenar por fecha (más reciente primero)
        $todos = $administrativas->merge($eticas)->sortByDesc('fecha')->values();

        return response()->json([
            'message' => 'Lista consolidada de descalificados.',
            'cantidad' => $todos->count(),
            'data' => $todos
        ]);
    }

    /**
     * Registrar una nueva descalificación administrativa.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_competidor' => 'required|integer|exists:competidor,id_competidor',
            'observaciones' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $validator->errors()
            ], 422);
        }

        $descalificacion = DescalificacionAdministrativa::create([
            'id_competidor' => $request->id_competidor,
            'observaciones' => $request->observaciones,
        ]);

        return response()->json([
            'message' => 'Competidor descalificado administrativamente con éxito.',
            'data' => $descalificacion
        ], 201);
    }
}