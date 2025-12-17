<?php

namespace App\Repositories;

use App\Model\Competencia;
use Illuminate\Support\Collection;

class CompetenciaRepository
{
    public function find(int $id): Competencia
    {
        return Competencia::findOrFail($id);
    }

    /**
     * Trae la competencia con todos sus hijos para el cálculo masivo.
     * Carga: Exámenes -> Evaluaciones.
     */
    public function findWithFullHierarchy(int $id): Competencia
    {
        return Competencia::with([
            'examenes.evaluaciones',
            'areaNivel'
        ])->findOrFail($id);
    }

    public function create(array $data): Competencia
    {
        return Competencia::create($data);
    }

    public function update(array $data, int $id): bool
    {
        $competencia = $this->find($id);
        return $competencia->update($data);
    }

    public function delete(int $id): bool
    {
        $competencia = $this->find($id);
        return $competencia->delete();
    }

    public function getAll(): Collection
    {
        return Competencia::with(['faseGlobal', 'areaNivel.areaOlimpiada.area', 'areaNivel.nivel'])->get();
    }

    /**
     * Obtiene solo los datos crudos de las competencias para la lista ligera.
     */
    public function getByResponsableAndArea(int $idResponsable, int $idArea): Collection
    {
        $resultados = Competencia::query()
            ->select([
                'id_competencia',
                'id_fase_global',
                'id_area_nivel',
                'fecha_inicio',
                'fecha_fin',
                'estado_fase',
                'criterio_clasificacion',
                'id_usuario_aval',
                'fecha_aval'
            ])
            ->whereHas('areaNivel.areaOlimpiada', function ($query) use ($idArea, $idResponsable) {
                $query->where('id_area', $idArea)
                      ->whereHas('responsableArea', function ($qResponsable) use ($idResponsable) {
                          $qResponsable->where('id_usuario', $idResponsable);
                      })
                      ->whereHas('olimpiada', function ($qOlimpiada) {
                          $qOlimpiada->where('estado', 1);
                      });
            })
            ->with(['areaNivel.nivel'])
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        return $resultados->map(function ($competencia) {
            return [
                'id_competencia' => $competencia->id_competencia,
                'id_fase_global' => $competencia->id_fase_global,
                'id_area_nivel'  => $competencia->id_area_nivel,
                'nivel'          => $competencia->areaNivel->nivel->nombre ?? 'Desconocido',
                'fecha_inicio'   => $competencia->fecha_inicio,
                'fecha_fin'      => $competencia->fecha_fin,
                'estado_fase'    => $competencia->estado_fase,
                'criterio_clasificacion' => $competencia->criterio_clasificacion,
                'id_usuario_aval' => $competencia->id_usuario_aval,
                'fecha_aval'     => $competencia->fecha_aval,
            ];
        });
    }

    /**
     * Obtiene los niveles asociados a un área específica
     * pero SOLO de la olimpiada que está activa (estado = 1).
     */
    public function getNivelesPorAreaActual(int $idArea): \Illuminate\Support\Collection
    {
        // Consultamos la tabla 'area_nivel'
        return \App\Model\AreaNivel::query()
            ->whereHas('areaOlimpiada', function ($q) use ($idArea) {
                $q->where('id_area', $idArea) // Filtro de Área
                  ->whereHas('olimpiada', function ($qOlim) {
                      $qOlim->where('estado', 1); // Filtro de Gestión Actual
                  });
            })
            ->with('nivel') // Cargamos el nombre del nivel (1ro Sec, etc.)
            ->get()
            ->map(function ($item) {
                // Transformamos al formato JSON solicitado
                return [
                    'id_area_nivel' => $item->id_area_nivel,
                    'nivel'         => $item->nivel->nombre
                ];
            });
    }

    /**
     * Obtiene todas las competencias creadas por un responsable en la gestión actual.
     * Carga las relaciones necesarias para construir el árbol de áreas/niveles.
     */
    public function getActivasPorResponsable(int $idResponsable): Collection
    {
        return Competencia::query()
            // 1. Filtro de Relaciones (Responsable y Olimpiada Actual)
            ->whereHas('areaNivel.areaOlimpiada', function ($q) use ($idResponsable) {
                $q->whereHas('responsableArea', function ($qResp) use ($idResponsable) {
                    $qResp->where('id_usuario', $idResponsable);
                })
                ->whereHas('olimpiada', function ($qOlim) {
                    $qOlim->where('estado', 1); // Solo gestión activa
                });
            })
            // 2. Cargar datos para el JSON (Area y Nivel)
            ->with([
                'areaNivel.nivel',
                'areaNivel.areaOlimpiada.area'
            ])
            ->get();
    }
}
