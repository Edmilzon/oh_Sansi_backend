<?php

namespace App\Repositories;

use App\Model\GradoEscolaridad;
use App\Model\Departamento;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ListaResponsableAreaRepository
{
    private function olimpiadaActiva()
    {
        return DB::table('olimpiada')
            ->select('id_olimpiada')
            ->where('estado', 1)
            ->limit(1);
    }

    public function getNivelesByArea(int $idArea): Collection
    {
        return DB::table('area_nivel as an')
            ->join('nivel as n', 'an.id_nivel', '=', 'n.id_nivel')
            ->join('area_olimpiada as ao', 'an.id_area_olimpiada', '=', 'ao.id_area_olimpiada')
            ->joinSub($this->olimpiadaActiva(), 'oa',
                fn ($join) => $join->on('ao.id_olimpiada', '=', 'oa.id_olimpiada')
            )
            ->select(
                'n.id_nivel',
                'n.nombre as nombre_nivel'
            )
            ->where('ao.id_area', $idArea)
            ->where('an.es_activo', 1)
            ->distinct()
            ->orderBy('n.nombre')
            ->get();
    }

    public function getAreaPorResponsable(int $idResponsable): Collection
    {
        return DB::table('responsable_area as ra')
            ->join('area_olimpiada as ao', 'ra.id_area_olimpiada', '=', 'ao.id_area_olimpiada')
            ->join('area as a', 'ao.id_area', '=', 'a.id_area')
            ->joinSub($this->olimpiadaActiva(), 'oa',
                fn ($join) => $join->on('ao.id_olimpiada', '=', 'oa.id_olimpiada')
            )
            ->select(
                'a.id_area',
                'a.nombre'
            )
            ->where('ra.id_usuario', $idResponsable)
            ->distinct()
            ->orderBy('a.nombre')
            ->get();
    }

    public function listarPorAreaYNivel(
        int $idResponsable,
        ?int $idArea = null,
        ?int $idNivel = null,
        ?int $idGrado = null,
        ?string $genero = null,
        ?string $departamento = null
    ): Collection {


        $areasDelResponsable = DB::table('responsable_area as ra')
            ->join('area_olimpiada as ao', 'ra.id_area_olimpiada', '=', 'ao.id_area_olimpiada')
            ->joinSub($this->olimpiadaActiva(), 'oa',
                fn ($join) => $join->on('ao.id_olimpiada', '=', 'oa.id_olimpiada')
            )
            ->where('ra.id_usuario', $idResponsable)
            ->pluck('ao.id_area')
            ->unique();

        if ($areasDelResponsable->isEmpty()) {
            return collect();
        }

        if ($genero && !in_array(strtolower($genero), ['m', 'f', 'masculino', 'femenino'])) {
            $departamento = $genero;
            $genero = null;
        }

        $query = DB::table('competidor as c')
            ->join('persona as p', 'c.id_persona', '=', 'p.id_persona')
            ->join('area_nivel as an', 'c.id_area_nivel', '=', 'an.id_area_nivel')
            ->join('area_olimpiada as ao', 'an.id_area_olimpiada', '=', 'ao.id_area_olimpiada')
            ->joinSub($this->olimpiadaActiva(), 'oa',
                fn ($join) => $join->on('ao.id_olimpiada', '=', 'oa.id_olimpiada')
            )
            ->join('area as a', 'ao.id_area', '=', 'a.id_area')
            ->join('nivel as n', 'an.id_nivel', '=', 'n.id_nivel')
            ->join('grado_escolaridad as g', 'c.id_grado_escolaridad', '=', 'g.id_grado_escolaridad')
            ->join('institucion as i', 'c.id_institucion', '=', 'i.id_institucion')
            ->join('departamento as d', 'c.id_departamento', '=', 'd.id_departamento')
            ->whereIn('a.id_area', $areasDelResponsable);

        if ($idArea)  $query->where('a.id_area', $idArea);
        if ($idNivel) $query->where('n.id_nivel', $idNivel);
        if ($idGrado) $query->where('g.id_grado_escolaridad', $idGrado);

        if ($genero) {
            $query->where('c.genero', strtoupper(substr($genero, 0, 1)));
        }

        if ($departamento) {
            is_numeric($departamento)
                ? $query->where('d.id_departamento', (int)$departamento)
                : $query->whereRaw(
                    'LOWER(d.nombre) LIKE ?',
                    ['%' . mb_strtolower($departamento) . '%']
                );
        }

        return $query->select(
                'p.apellido',
                'p.nombre',
                DB::raw("
                    CASE c.genero
                        WHEN 'M' THEN 'Masculino'
                        WHEN 'F' THEN 'Femenino'
                        ELSE c.genero
                    END AS genero
                "),
                'p.ci',
                'i.nombre as colegio',
                'd.nombre as departamento',
                'a.nombre as area',
                'n.nombre as nivel',
                'g.nombre as grado'
            )
            ->orderBy('p.apellido')
            ->orderBy('p.nombre')
            ->get();
    }
    public function getCompetidoresPorAreaYNivel(int $id_competencia, int $idArea, int $idNivel): Collection
    {
        $gestionActual = date('Y');

        $competidores = DB::table('competidor')
            ->join('persona', 'competidor.id_persona', '=', 'persona.id_persona')
            ->join('institucion', 'competidor.id_institucion', '=', 'institucion.id_institucion')
            ->join('departamento', 'competidor.id_departamento', '=', 'departamento.id_departamento')
            ->join('grado_escolaridad', 'competidor.id_grado_escolaridad', '=', 'grado_escolaridad.id_grado_escolaridad')
            ->join('area_nivel', 'competidor.id_area_nivel', '=', 'area_nivel.id_area_nivel')
            ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
            ->join('area_olimpiada', 'area_nivel.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
            ->join('area', 'area_olimpiada.id_area', '=', 'area.id_area')
            ->join('olimpiada', 'area_olimpiada.id_olimpiada', '=', 'olimpiada.id_olimpiada')
            ->where('area.id_area', $idArea)
            ->leftJoin('descalificacion_administrativa as da', 'competidor.id_competidor', '=', 'da.id_competidor')
            ->where('nivel.id_nivel', $idNivel)
            ->where('olimpiada.gestion', $gestionActual)
            ->where('area_nivel.es_activo', true)
            ->select(
                'competidor.id_competidor',
                'persona.nombre',
                'persona.apellido',
                'persona.ci',
                'persona.telefono',
                'persona.email',
                DB::raw("CASE
                            WHEN competidor.genero = 'M' THEN 'Masculino'
                            WHEN competidor.genero = 'F' THEN 'Femenino'
                            ELSE competidor.genero
                        END AS genero"),
                'institucion.nombre as colegio',
                'departamento.nombre as departamento',
                'grado_escolaridad.nombre as grado',
                'area.nombre as area',
                'nivel.nombre as nivel',
                DB::raw("CASE
                            WHEN da.id_descalificacion IS NOT NULL THEN 'descalificado'
                            ELSE 'disponible para calificar'
                        END AS estado_competidor"),
                'da.observaciones as observaciones_descalificacion',
                 'competidor.id_persona',
                'competidor.id_area_nivel',
                'competidor.id_grado_escolaridad',
                'competidor.id_institucion',
                'competidor.id_departamento'
            )
            ->orderBy('persona.apellido')
            ->orderBy('persona.nombre')
            ->get();

        if ($competidores->isEmpty()) {
            return collect();
        }

        $competidorIds = $competidores->pluck('id_competidor');

        $evaluaciones = DB::table('evaluacion')
            ->join('examen_conf', 'evaluacion.id_examen_conf', '=', 'examen_conf.id_examen_conf')
            ->whereIn('id_competidor', $competidorIds)
            ->where('examen_conf.id_competencia', $id_competencia)
            ->select(
                'id_evaluacion',
                'evaluacion.nota',
                'evaluacion.observacion',
                'evaluacion.fecha',
                'evaluacion.estado',
                'evaluacion.id_competidor',
                'evaluacion.id_evaluador_an'
            )
            ->get()
            ->groupBy('id_competidor');

        return $competidores->map(function ($competidor) use ($evaluaciones) {
            $competidor->evaluaciones = $evaluaciones->get($competidor->id_competidor, collect());
            return $competidor;
        });
    }

   public function getListaGradosPorAreaNivel(int $idArea, int $idNivel): Collection
    {
    if ($idArea <= 0 || $idNivel <= 0) {
        return collect();
    }

    $gestionActual = date('Y');

    $gradoIds = DB::table('area_nivel_grado')
        ->join('area_nivel', 'area_nivel_grado.id_area_nivel', '=', 'area_nivel.id_area_nivel')
        ->join('area_olimpiada', 'area_nivel.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
        ->join('olimpiada', 'area_olimpiada.id_olimpiada', '=', 'olimpiada.id_olimpiada')
        ->where('area_olimpiada.id_area', $idArea)
        ->where('area_nivel.id_nivel', $idNivel)
        ->where('area_nivel.es_activo', true)
        ->where('olimpiada.gestion', $gestionActual)
        ->pluck('area_nivel_grado.id_grado_escolaridad')
        ->unique()
        ->values();

    if ($gradoIds->isEmpty()) {
        return collect();
    }

    return GradoEscolaridad::whereIn('id_grado_escolaridad', $gradoIds)
        ->orderBy('nombre')
        ->get();
    }

     public function getListaDepartamento()
    {
    return Departamento::all();
    }

    public function getListaGeneros(): array
    {
    return [
        ['id' => 'M', 'nombre' => 'Masculino'],
        ['id' => 'F', 'nombre' => 'Femenino']
    ];
    }
}
