<?php

namespace App\Repositories;

use App\Model\AreaNivel;
use App\Model\AreaOlimpiada;
use App\Model\Competencia;
use App\Model\Fase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FaseRepository
{
    public function obtenerPorAreaNivel(int $id_area_nivel): Collection
    {
        return Fase::where('id_area_nivel', $id_area_nivel)->orderBy('orden')->get();
    }

    public function crearConCompetencia(array $data): Fase
    {
        return DB::transaction(function () use ($data) {
            // 1. Crear la Fase
            $fase = Fase::create([
                'nombre' => $data['nombre'],
                'orden' => $data['orden'] ?? 1,
                'id_area_nivel' => $data['id_area_nivel'],
            ]);

            // 2. Encontrar el Responsable de Área
            $areaNivel = AreaNivel::findOrFail($data['id_area_nivel']);
            $areaOlimpiada = AreaOlimpiada::where('id_area', $areaNivel->id_area)
                ->where('id_olimpiada', $areaNivel->id_olimpiada)
                ->firstOrFail();

            $responsableArea = DB::table('responsable_area')
                ->where('id_area_olimpiada', $areaOlimpiada->id_area_olimpiada)
                ->first();

            if (!$responsableArea) {
                throw new \Exception("No se encontró un responsable para el área de esta fase.");
            }

            // 3. Crear la Competencia
            Competencia::create([
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => $data['fecha_fin'],
                'estado' => $data['estado'],
                'id_fase' => $fase->id_fase,
                'id_responsableArea' => $responsableArea->id_responsableArea,
            ]);

            return $fase->load('competencias');
        });
    }

    public function obtenerPorId(int $id_fase): ?Fase
    {
        return Fase::find($id_fase);
    }

    public function actualizar(int $id_fase, array $data): bool
    {
        $fase = Fase::find($id_fase);
        if ($fase) {
            return $fase->update($data);
        }
        return false;
    }

    public function eliminar(int $id_fase): bool
    {
        $fase = Fase::find($id_fase);
        if ($fase) {
            return $fase->delete();
        }
        return false;
    }
}
