<?php

namespace App\Repositories;

use App\Model\AreaNivel;
use App\Model\Area;
use App\Model\Nivel;
use App\Model\NivelGrado;
use Illuminate\Database\Eloquent\Collection;

class AreaNivelRepository
{
    public function getAllAreasNiveles(): Collection
    {
        return AreaNivel::with([
            'area', 
            'nivel', 
            'olimpiada', 
            'nivelGrado.gradoEscolaridad'
        ])->get();
    }

    public function getByArea(int $id_area, ?int $idOlimpiada = null): Collection
    {
        $query = AreaNivel::with(['nivelGrado.gradoEscolaridad'])
            ->where('id_area', $id_area);
        
        if ($idOlimpiada) {
            $query->where('id_olimpiada', $idOlimpiada);
        }
        
        return $query->get();
    }

    public function getByAreaAll(int $id_area, ?int $idOlimpiada = null): Collection
    {
        $query = AreaNivel::with([
            'area:id_area,nombre',
            'nivel:id_nivel,nombre', 
            'olimpiada:id_olimpiada,gestion',
            'nivelGrado.gradoEscolaridad'
        ])
        ->where('id_area', $id_area);

        if ($idOlimpiada) {
            $query->where('id_olimpiada', $idOlimpiada);
        }
        return $query->get();
    }

    public function getAreaNivelAsignadosAll(int $idOlimpiada): Collection
    {
        return Area::with([
            'areaNiveles' => function($query) use ($idOlimpiada) {
                $query->where('id_olimpiada', $idOlimpiada)
                      ->with(['nivel:id_nivel,nombre', 'nivelGrado.gradoEscolaridad']);
            }
        ])
        ->get(['id_area', 'nombre']);
    }
    
    public function getById(int $id): ?AreaNivel
    {
        return AreaNivel::with([
            'area', 
            'nivel', 
            'olimpiada', 
            'nivelGrado.gradoEscolaridad'
        ])->find($id);
    }

    public function update(int $id, array $data): bool
    {
        $areaNivel = AreaNivel::find($id);
        
        if (!$areaNivel) {
            return false;
        }

        return $areaNivel->update($data);
    }

    public function delete(int $id): bool
    {
        $areaNivel = AreaNivel::find($id);
        
        if (!$areaNivel) {
            return false;
        }

        return $areaNivel->delete();
    }
    
    public function getByAreaAndNivel(int $id_area, int $id_nivel, int $id_olimpiada): ?AreaNivel
    {
        return AreaNivel::with(['nivelGrado.gradoEscolaridad'])
            ->where('id_area', $id_area)
            ->where('id_nivel', $id_nivel)
            ->where('id_olimpiada', $id_olimpiada)
            ->first();
    }

    public function createAreaNivel(array $data): AreaNivel
    {
        return AreaNivel::create($data);
    }

    public function getAreasConNivelesSimplificado(int $idOlimpiada): Collection
    {
        return Area::with([
            'areaNiveles' => function($query) use ($idOlimpiada) {
                $query->where('id_olimpiada', $idOlimpiada)
                      ->where('activo', true)
                      ->with([
                          'nivel:id_nivel,nombre', 
                          'nivelGrado' => function($q) {
                              $q->where('activo', true)->with('gradoEscolaridad');
                          }
                      ]);
            }
        ])
        ->whereHas('areaNiveles', function($query) use ($idOlimpiada) {
            $query->where('id_olimpiada', $idOlimpiada)
                  ->where('activo', true);
        })
        ->get(['id_area', 'nombre']);
    }

    public function getActualesByOlimpiada(int $idOlimpiada): Collection
    {
        return Area::with([
            'areaNiveles' => function($query) use ($idOlimpiada) {
                $query->where('id_olimpiada', $idOlimpiada)
                      ->where('activo', true)
                      ->with([
                          'nivel:id_nivel,nombre', 
                          'nivelGrado' => function($q) {
                              $q->where('activo', true)->with('gradoEscolaridad');
                          }
                      ]);
            }
        ])
        ->whereHas('areaNiveles', function($query) use ($idOlimpiada) {
            $query->where('id_olimpiada', $idOlimpiada)
                  ->where('activo', true);
        })
        ->get(['id_area', 'nombre']);
    }

    // Métodos para NivelGrado
    public function createNivelGrado(array $data): NivelGrado
    {
        return NivelGrado::create($data);
    }

    public function getNivelGradoByAreaNivelAndGrado(int $id_area_nivel, int $id_grado_escolaridad): ?NivelGrado
    {
        return NivelGrado::where('id_area_nivel', $id_area_nivel)
            ->where('id_grado_escolaridad', $id_grado_escolaridad)
            ->first();
    }

    public function updateNivelGrado(int $id, array $data): bool
    {
        $nivelGrado = NivelGrado::find($id);
        
        if (!$nivelGrado) {
            return false;
        }

        return $nivelGrado->update($data);
    }

    public function getAreaNivelesByOlimpiada(int $idOlimpiada): Collection
    {
        return AreaNivel::with([
            'area', 
            'nivel', 
            'nivelGrado.gradoEscolaridad'
        ])
        ->where('id_olimpiada', $idOlimpiada)
        ->get();
    }

    public function getNivelesGradosByAreaNivel(int $id_area_nivel): Collection
    {
        return NivelGrado::with('gradoEscolaridad')
            ->where('id_area_nivel', $id_area_nivel)
            ->get();
    }
}