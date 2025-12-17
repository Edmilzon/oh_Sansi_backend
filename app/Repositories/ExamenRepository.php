<?php

namespace App\Repositories;

use App\Model\Examen;
use Illuminate\Database\Eloquent\Collection;

class ExamenRepository
{
    public function find(int $id): Examen
    {
        return Examen::findOrFail($id);
    }

    public function create(array $data): Examen
    {
        return Examen::create($data);
    }

    public function update(array $data, int $id): bool
    {
        $examen = $this->find($id);
        return $examen->update($data);
    }

    public function delete(int $id): bool
    {
        $examen = $this->find($id);
        return $examen->delete();
    }

    /**
     * Obtiene los exámenes de un Área-Nivel específico.
     * Retorna una estructura plana y limpia para el listado.
     */
    public function getByAreaNivel(int $idAreaNivel): Collection
    {
        return Examen::query()
            ->select([
                'id_examen',
                'id_competencia',
                'nombre',
                'ponderacion',
                'maxima_nota',
                'fecha_hora_inicio',
                'tipo_regla',
                'configuracion_reglas',
                'estado_ejecucion',
                'fecha_inicio_real'
            ])
            ->whereHas('competencia', function ($q) use ($idAreaNivel) {
                $q->where('id_area_nivel', $idAreaNivel)
                  ->whereHas('areaNivel.areaOlimpiada.olimpiada', function ($qOlimpiada) {
                      $qOlimpiada->where('estado', 1);
                  });
            })
            ->get();
    }

    public function getSimpleByAreaNivel(int $idAreaNivel): Collection
    {
        return Examen::query()
            ->select('id_examen', 'nombre')
            ->whereHas('competencia', function ($q) use ($idAreaNivel) {
                $q->where('id_area_nivel', $idAreaNivel)
                ->whereHas('areaNivel.areaOlimpiada.olimpiada', function ($qOlim) {
                    $qOlim->where('estado', 1);
                });
            })
            ->get();
    }

    public function getCompetidoresDeExamen(int $idExamen): Collection
    {
        return \App\Model\Evaluacion::where('id_examen', $idExamen)
            ->with([
                'competidor.persona',
                'competidor.gradoEscolaridad',
                'usuarioBloqueo.persona'
            ])
            ->get();
    }

    /**
     * Suma las ponderaciones de los exámenes de una competencia.
     * @param int $competenciaId
     * @param int|null $excludeId (Opcional) ID para excluir al editar (no sumarse a sí mismo)
     */
    public function sumarPonderaciones(int $competenciaId, ?int $excludeId = null): float
    {
        $query = \App\Model\Examen::where('id_competencia', $competenciaId);

        if ($excludeId) {
            $query->where('id_examen', '!=', $excludeId);
        }

        return (float) $query->sum('ponderacion');
    }
}
