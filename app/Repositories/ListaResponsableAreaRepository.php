<?php

namespace App\Repositories;

use App\Model\Area;
use App\Model\Nivel;
use App\Model\ResponsableArea;
use App\Model\Competidor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ListaResponsableAreaRepository
{
    public function getNivelesByArea(int $idArea): Collection
    {
        $area = Area::find($idArea);

        if (! $area) {
            return collect();
        }

        return $area->niveles()
            ->select('nivel.id_nivel', 'nivel.nombre') 
            ->distinct()
            ->get()
            ->map(function($nivel) {
                return [
                'id_nivel' => $nivel->id_nivel,
                'nombre' => $nivel->nombre
                ];
             });

    }

    public function getAreaPorResponsable(int $idUsuario): Collection
    {
        return ResponsableArea::where('id_usuario', $idUsuario)
            ->join('area_olimpiada', 'responsable_area.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
            ->join('area', 'area_olimpiada.id_area', '=', 'area.id_area')
            ->select('area.id_area', 'area.nombre')
            ->distinct()
            ->get();
    }
   
  public function ListarPorAreaYNivel(int $idResponsable, int $idArea, int $idNivel): Collection
{
    // 1. Obtener todos los competidores del responsable (1/0/0)
    $listaBase = DB::table('responsable_area')
        ->join('area_olimpiada', 'responsable_area.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
        ->join('area', 'area_olimpiada.id_area', '=', 'area.id_area')
        ->where('responsable_area.id_usuario', $idResponsable)
        ->pluck('area.id_area');

    if ($listaBase->isEmpty()) {
        return collect();
    }

    $competidores = Competidor::join('area_nivel', 'competidor.id_area_nivel', '=', 'area_nivel.id_area_nivel')
        ->join('area', 'area_nivel.id_area', '=', 'area.id_area')
        ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
        ->select('competidor.datos', 'area.nombre as area', 'nivel.nombre as nivel')
        ->whereIn('area.id_area', $listaBase)
        ->get()
        ->map(function ($c) {
            $datos = $this->decodeDatos($c->datos);
            return [
                'nombre'   => $datos['nombre']   ?? null,
                'apellido' => $datos['apellido'] ?? null,
                'ci'       => $datos['ci']       ?? $datos['carnet'] ?? null,
                'grado'    => $datos['grado']    ?? null,
                'area'     => $c->area,
                'nivel'    => $c->nivel,
            ];
        });

    // 2. Filtrar sobre la lista base según parámetros
    $competidoresFiltrados = $competidores->filter(function ($c) use ($idArea, $idNivel) {
        if ($idArea != 0 && $c['area'] !== Area::find($idArea)->nombre) return false;
        if ($idNivel != 0 && $c['nivel'] !== Nivel::find($idNivel)->nombre) return false;
        return true;
    });

    return $competidoresFiltrados->values(); // reindexar
}

private function decodeDatos($datosRaw): array
{
    if (empty($datosRaw)) return [];

    $datos = json_decode($datosRaw, true);

    if (is_string($datos)) {
        $datos2 = json_decode($datos, true);
        if (is_array($datos2)) $datos = $datos2;
    }

    return is_array($datos) ? $datos : [];
}
}