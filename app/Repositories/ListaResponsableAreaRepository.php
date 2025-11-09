<?php

namespace App\Repositories;

use App\Model\GradoEscolaridad;
use App\Model\Departamento;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ListaResponsableAreaRepository
{
    public function getNivelesByArea(int $idArea): Collection
    {
        return DB::table('area_nivel')
            ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
            ->where('area_nivel.id_area', $idArea)
            ->select('nivel.id_nivel', 'nivel.nombre')
            ->distinct()
            ->orderBy('nivel.nombre')
            ->get();
    }

    public function getAreaPorResponsable(int $idResponsable): Collection
    {
        return DB::table('responsable_area')
            ->join('area_olimpiada', 'responsable_area.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
            ->join('area', 'area_olimpiada.id_area', '=', 'area.id_area')
            ->select('area.id_area', 'area.nombre')
            ->where('responsable_area.id_usuario', $idResponsable)
            ->distinct()
            ->orderBy('area.nombre')
            ->get();
    }

    /**
     * Lista los competidores filtrando por área/nivel/grado 
     */
   public function listarPorAreaYNivel(
    int $idResponsable, 
    ?int $idArea, 
    ?int $idNivel, 
    ?int $idGrado, 
    ?string $genero = null,
    ?string $departamento = null
): Collection
{
    $areasDelResponsable = DB::table('responsable_area')
        ->join('area_olimpiada', 'responsable_area.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
        ->where('responsable_area.id_usuario', $idResponsable)
        ->pluck('area_olimpiada.id_area')
        ->unique()
        ->values();

    if ($areasDelResponsable->isEmpty()) {
        return collect();
    }

    if ($genero && !in_array(strtolower($genero), ['m', 'f', 'masculino', 'femenino'])) {
        // Es un departamento, no un género
        $departamento = $genero;
        $genero = null;
    }

    $query = DB::table('competidor')
        ->join('persona', 'competidor.id_persona', '=', 'persona.id_persona')
        ->join('area_nivel', 'competidor.id_area_nivel', '=', 'area_nivel.id_area_nivel')
        ->join('area', 'area_nivel.id_area', '=', 'area.id_area')
        ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
        ->join('grado_escolaridad', 'competidor.id_grado_escolaridad', '=', 'grado_escolaridad.id_grado_escolaridad')
        ->join('institucion', 'competidor.id_institucion', '=', 'institucion.id_institucion')
        ->whereIn('area.id_area', $areasDelResponsable);

    if ($idArea && $idArea !== 0) {
        $query->where('area.id_area', $idArea);
    }

    if ($idNivel && $idNivel !== 0) {
        $query->where('nivel.id_nivel', $idNivel);
    }

    if ($idGrado && $idGrado !== 0) {
        $query->where('grado_escolaridad.id_grado_escolaridad', $idGrado);
    }

    if ($genero) {
        $genero = strtolower($genero);
        if (in_array($genero, ['m', 'masculino'])) {
            $query->where('persona.genero', 'M');
        } elseif (in_array($genero, ['f', 'femenino'])) {
            $query->where('persona.genero', 'F');
        }
    }

    if ($departamento) {
        $query->whereRaw('LOWER(competidor.departamento) = ?', [strtolower($departamento)]);
    }

    return $query->select(
            'persona.apellido',
            'persona.nombre',
            DB::raw("CASE 
                        WHEN persona.genero = 'M' THEN 'Masculino'
                        WHEN persona.genero = 'F' THEN 'Femenino'
                        ELSE persona.genero
                    END AS genero"),
            'persona.ci',
            'competidor.departamento',
            'institucion.nombre as colegio',
            'area.nombre as area',
            'nivel.nombre as nivel',
            'grado_escolaridad.nombre as grado'
        )
        ->orderBy('persona.apellido')
        ->orderBy('persona.nombre')
        ->get();
}

    public function getCompetidoresPorAreaYNivel(int $idArea, int $idNivel): Collection
    {
        $query = DB::table('competidor')
            ->join('persona', 'competidor.id_persona', '=', 'persona.id_persona')
            ->join('area_nivel', 'competidor.id_area_nivel', '=', 'area_nivel.id_area_nivel')
            ->join('area', 'area_nivel.id_area', '=', 'area.id_area')
            ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
            ->join('grado_escolaridad', 'competidor.id_grado_escolaridad', '=', 'grado_escolaridad.id_grado_escolaridad')
            ->join('institucion', 'competidor.id_institucion', '=', 'institucion.id_institucion')
            ->where('area.id_area', $idArea)
            ->where('nivel.id_nivel', $idNivel);

        return $query->select(
                'persona.apellido',
                'persona.nombre',
                DB::raw("CASE 
                            WHEN persona.genero = 'M' THEN 'Masculino'
                            WHEN persona.genero = 'F' THEN 'Femenino'
                            ELSE persona.genero
                        END AS genero"),
                'persona.ci',
                'competidor.departamento',
                'institucion.nombre as colegio',
                'area.nombre as area',
                'nivel.nombre as nivel',
                'grado_escolaridad.nombre as grado'
            )
            ->orderBy('persona.apellido')
            ->orderBy('persona.nombre')
            ->get();
    }

    public function getListaGrados(){
        return GradoEscolaridad::all();
    }

     public function getListaDepartamento()
{
    return Departamento::all();
}
public function getListaGeneros(): array
{
    // Retorna un array de géneros
    return [
        ['id' => 'M', 'nombre' => 'Masculino'],
        ['id' => 'F', 'nombre' => 'Femenino']
    ];
}


}
