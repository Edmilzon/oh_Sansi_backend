<?php

namespace App\Repositories;

use App\Model\Medallero;
use App\Model\Evaluacion;
use App\Model\LogCambioNota;
use App\Model\Area;
use App\Model\Nivel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReporteRepository
{
    public function getMedallero(int $idCompetencia)
    {
        return Medallero::where('id_competencia', $idCompetencia)
            ->with(['competidor.persona', 'competidor.institucion'])
            ->orderBy('puesto')
            ->get()
            ->map(function ($item) {
                return [
                    'puesto'      => $item->puesto,
                    'medalla'     => $item->medalla,
                    'nombre'      => $this->nombreCompleto($item->competidor->persona),
                    'institucion' => $item->competidor->institucion->nombre,
                ];
            });
    }

    public function getClasificados(int $idCompetencia)
    {
        return Evaluacion::whereHas('examen', function ($q) use ($idCompetencia) {
                $q->where('id_competencia', $idCompetencia);
            })
            ->where('resultado_calculado', 'CLASIFICADO')
            ->with(['competidor.persona', 'competidor.institucion', 'examen'])
            ->get()
            ->map(function ($item) {
                return [
                    'examen'      => $item->examen->nombre,
                    'nombre'      => $this->nombreCompleto($item->competidor->persona),
                    'institucion' => $item->competidor->institucion->nombre,
                    'nota'        => $item->nota,
                    'resultado'   => $item->resultado_calculado,
                ];
            });
    }

    public function getLogCambios(int $idEvaluacion)
    {
        return LogCambioNota::where('id_evaluacion', $idEvaluacion)
            ->with('autor.persona')
            ->orderByDesc('fecha_cambio')
            ->get()
            ->map(function ($log) {
                return [
                    'fecha'         => $log->fecha_cambio->format('d/m/Y H:i'),
                    'autor'         => $this->nombreCompleto($log->autor->persona),
                    'nota_anterior' => $log->nota_anterior,
                    'nota_nueva'    => $log->nota_nueva,
                    'motivo'        => $log->motivo_cambio,
                ];
            });
    }

    public function getHistorialCompleto(int $page, int $limit, array $filtros): array
    {
        $query = LogCambioNota::query()
            ->with([
                'autor.persona',
                'evaluacion.competidor.persona',
                'evaluacion.competidor.institucion',
                'evaluacion.competidor.gradoEscolaridad',
                'evaluacion.examen.competencia.areaNivel.area',
                'evaluacion.examen.competencia.areaNivel.nivel',
                'evaluacion.examen.competencia.areaNivel.areaOlimpiada.olimpiada'
            ]);

        $query->whereHas('evaluacion.examen.competencia.areaNivel.areaOlimpiada.olimpiada', function ($q) {
            $q->where('estado', true);
        });

        if (!empty($filtros['id_area'])) {
            $query->whereHas('evaluacion.examen.competencia.areaNivel', function ($q) use ($filtros) {
                $q->where('id_area', $filtros['id_area']);
            });
        }
        if (!empty($filtros['ids_niveles'])) {
            $ids = is_array($filtros['ids_niveles']) ? $filtros['ids_niveles'] : explode(',', $filtros['ids_niveles']);

            $query->whereHas('evaluacion.examen.competencia.areaNivel', function ($q) use ($ids) {
                $q->whereIn('id_nivel', $ids);
            });
        }

        if (!empty($filtros['search'])) {
            $term = $filtros['search'];
            $query->where(function ($subQ) use ($term) {
                $subQ->whereHas('autor.persona', function ($q) use ($term) {
                    $q->where(DB::raw("CONCAT(nombre, ' ', apellido)"), 'LIKE', "%{$term}%");
                })
                ->orWhereHas('evaluacion.competidor.persona', function ($q) use ($term) {
                    $q->where(DB::raw("CONCAT(nombre, ' ', apellido)"), 'LIKE', "%{$term}%");
                });
            });
        }

        $query->orderBy('fecha_cambio', 'desc');

        $paginator = $query->paginate($limit, ['*'], 'page', $page);

        $items = $paginator->getCollection()->map(function ($log) {
            $compData = $log->evaluacion->examen->competencia->areaNivel ?? null;
            $diff = $log->nota_nueva - $log->nota_anterior;
            [$accion, $tipoCambio] = $this->determinarAccion($log->nota_anterior, $log->nota_nueva);

            return [
                'id_historial'      => $log->id_log_cambio_nota,
                'fecha_hora'        => Carbon::parse($log->fecha_cambio)->toIso8601String(),
                'nombre_evaluador'  => $this->nombreCompleto($log->autor->persona ?? null),
                'nombre_olimpista'  => $this->nombreCompleto($log->evaluacion->competidor->persona ?? null),
                'institucion'       => $log->evaluacion->competidor->institucion->nombre ?? 'Sin Institución',
                'area'              => $compData->area->nombre ?? 'N/A',
                'nivel'             => $compData->nivel->nombre ?? 'N/A',
                'grado_escolaridad' => $log->evaluacion->competidor->gradoEscolaridad->nombre ?? 'N/A',
                'id_area'           => $compData->id_area ?? null,
                'id_nivel'          => $compData->id_nivel ?? null,
                'accion'            => $accion,
                'tipo_cambio'       => $tipoCambio,
                'nota_anterior'     => (float) $log->nota_anterior,
                'nota_nueva'        => (float) $log->nota_nueva,
                'diferencia'        => (float) $diff,
                'observacion'       => $log->motivo_cambio,
                'descripcion'       => $this->generarFrase($accion, $log->nota_anterior, $log->nota_nueva)
            ];
        });

        return [
            'success' => true,
            'data'    => $items,
            'meta'    => [
                'total'      => $paginator->total(),
                'page'       => $paginator->currentPage(),
                'limit'      => $paginator->perPage(),
                'totalPages' => $paginator->lastPage(),
            ]
        ];
    }

    public function getAreasActivas()
    {
        return Area::query()
            ->whereHas('areaOlimpiadas.olimpiada', function ($q) {
                $q->where('estado', true);
            })
            ->select('id_area', 'nombre')
            ->orderBy('nombre')
            ->get();
    }

    public function getNivelesActivosPorArea(int $idArea)
    {
        return Nivel::query()
            ->whereHas('areaNiveles', function ($q) use ($idArea) {
                $q->where('id_area', $idArea)
                ->whereHas('areaOlimpiada.olimpiada', function ($q2) {
                    $q2->where('estado', true);
                });
            })
            ->select('id_nivel', 'nombre')
            ->orderBy('nombre')
            ->get();
    }

    private function determinarAccion($ant, $nue): array
    {
        if ($ant == 0 && $nue > 0) return ['Calificar', 'CREATE'];
        if ($nue == 0 && $ant > 0) return ['Desclasificar', 'DELETE'];
        return ['Modificar', 'UPDATE'];
    }

    private function nombreCompleto($persona): string
    {
        return $persona ? "{$persona->nombre} {$persona->apellido}" : 'Desconocido';
    }

    private function generarFrase($accion, $ant, $nue): string
    {
        return match ($accion) {
            'Calificar'     => "Calificación inicial asignada: {$nue} pts.",
            'Desclasificar' => "Participante desclasificado. Nota anulada.",
            default         => "Nota modificada de {$ant} a {$nue} pts."
        };
    }
}
