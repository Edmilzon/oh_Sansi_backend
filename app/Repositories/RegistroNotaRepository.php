<?php

namespace App\Repositories;

use App\Model\RegistroNota;
use App\Model\Area;
use App\Model\AreaNivel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RegistroNotaRepository
{
    public function getHistorialCalificaciones(
        ?int $id_area = null,
        ?array $ids_niveles = null,
        int $page = 1,
        int $limit = 10
    ): LengthAwarePaginator {
        $query = RegistroNota::with([
            'evaluador.usuario',
            'competidor.persona',
            'areaNivel.area',
            'areaNivel.nivel'
        ]);

        // Aplicar filtros
        if ($id_area) {
            $query->whereHas('areaNivel', function($q) use ($id_area) {
                $q->where('id_area', $id_area);
            });
        }

        if ($ids_niveles && !empty($ids_niveles)) {
            $query->whereHas('areaNivel', function($q) use ($ids_niveles) {
                $q->whereIn('id_nivel', $ids_niveles);
            });
        }

        // Ordenar por fecha más reciente primero
        $query->orderBy('created_at', 'desc');

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function getAreasParaFiltro(): Collection
    {
        return Area::select('id_area', 'nombre')
            ->orderBy('nombre')
            ->get();
    }

    public function getNivelesPorArea(int $id_area): Collection
    {
        return AreaNivel::with('nivel:id_nivel,nombre')
            ->where('id_area', $id_area)
            ->where('activo', true)
            ->get()
            ->map(function($areaNivel) {
                return [
                    'id_nivel' => $areaNivel->nivel->id_nivel,
                    'nombre' => $areaNivel->nivel->nombre
                ];
            })
            ->unique('id_nivel')
            ->values();
    }

    public function createRegistroNota(array $data): RegistroNota
    {
        return RegistroNota::create($data);
    }

    public function getById(int $id): ?RegistroNota
    {
        return RegistroNota::with([
            'evaluador.usuario',
            'competidor.persona',
            'areaNivel.area',
            'areaNivel.nivel'
        ])->find($id);
    }

    public function getByCompetidor(int $id_competidor): Collection
    {
        return RegistroNota::with([
            'evaluador.usuario',
            'areaNivel.area',
            'areaNivel.nivel'
        ])
        ->where('id_competidor', $id_competidor)
        ->orderBy('created_at', 'desc')
        ->get();
    }

    public function getByAreaNivel(int $id_area_nivel): Collection
    {
        return RegistroNota::with([
            'evaluador.usuario',
            'competidor.persona'
        ])
        ->where('id_area_nivel', $id_area_nivel)
        ->orderBy('created_at', 'desc')
        ->get();
    }
}