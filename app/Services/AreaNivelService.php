<?php

namespace App\Services;
use App\Models\AreaNivel;
use App\Models\Olimpiada;
use App\Repositories\AreaNivelRepository;
use Illuminate\Database\Eloquent\Collection;

class AreaNivelService {
    protected $areaNivelRepository;

    public function __construct(AreaNivelRepository $areaNivelRepository){
        $this->areaNivelRepository = $areaNivelRepository;
    }

    private function obtenerOlimpiadaActual()
    {
        $gestionActual = date('Y');
        $nombreOlimpiada = "Olimpiadas Oh! Sansi $gestionActual";
        
        return Olimpiada::firstOrCreate(
            ['gestion' => "Gestión $gestionActual"],
            ['nombre' => $nombreOlimpiada]
        );
    }

    public function getAreaNivelList(){
        return $this->areaNivelRepository->getAllAreasNiveles();
    }

    public function getAreaNivelByArea(int $id_area){
        $olimpiadaActual = $this->obtenerOlimpiadaActual();

        $areaNiveles = $this->areaNivelRepository->getByArea($id_area, $olimpiadaActual->id_olimpiada);

         return $areaNiveles->map(function($areaNivel) {
            return [
                'id_area_nivel' => $areaNivel->id_area_nivel,
                'id_area' => $areaNivel->id_area,
                'id_nivel' => $areaNivel->id_nivel,
                'id_olimpiada' => $areaNivel->id_olimpiada,
                'activo' => $areaNivel->activo,
                'created_at' => $areaNivel->created_at,
                'updated_at' => $areaNivel->updated_at
            ];
        });
    }

    public function getAreaNivelById(int $id): ?array
    {
        $areaNivel = $this->areaNivelRepository->getById($id);
        
        if (!$areaNivel) {
            return null;
        }

        return [
            'area_nivel' => $areaNivel,
            'message' => 'Relación área-nivel encontrada'
        ];
    }

    public function getAreaNivelByAreaAll(int $id_area): Collection
    {
        $olimpiadaActual = $this->obtenerOlimpiadaActual();
        return $this->areaNivelRepository->getByAreaAll($id_area, $olimpiadaActual->id_olimpiada);
    }

    public function getAreaNivelesAsignadosAll(): array
    {

        $olimpiadaActual = $this->obtenerOlimpiadaActual();
        $areas = $this->areaNivelRepository->getAreaNivelAsignadosAll($olimpiadaActual->id_olimpiada);
    
        $resultado = $areas->filter(function($area) {
        if ($area->areaNiveles->isEmpty()) {
            return true;
        }
        
        return $area->areaNiveles->contains('activo', true);
    })->map(function($area) {
        
        if ($area->areaNiveles->isEmpty()) {
            return [
                'id_area' => $area->id_area,
                'nombre' => $area->nombre,
                'niveles' => []
            ];
        }
        
        $nivelesArray = $area->areaNiveles->filter(function($areaNivel) {
            return $areaNivel->activo === true;
        })->map(function($areaNivel) {
            return [
                'id_nivel' => $areaNivel->nivel->id_nivel,
                'nombre' => $areaNivel->nivel->nombre,
                'asignado_activo' => $areaNivel->activo
            ];
        });
        
        return [
            'id_area' => $area->id_area,
            'nombre' => $area->nombre,
            'niveles' => $nivelesArray->values()
        ];
        });
    
    return [
        'areas' => $resultado->values(),
        'olimpiada_actual' => $olimpiadaActual->gestion,
        'message' => 'Se muestran las áreas que tienen al menos una relación activa o no tienen relaciones'
    ];
    }

    public function createNewAreaNivel(array $data){
        $olimpiadaActual = $this->obtenerOlimpiadaActual();
        
        $existing = AreaNivel::where('id_area', $data['id_area'])
                            ->where('id_nivel', $data['id_nivel'])
                            ->where('id_olimpiada', $olimpiadaActual->id_olimpiada)
                            ->first();

        if ($existing) {
            throw new \Exception('Ya existe este nivel asociado a esta área en la olimpiada actual.');
        }

        $data['id_olimpiada'] = $olimpiadaActual->id_olimpiada;
        $areaNivel = $this->areaNivelRepository->createAreaNivel($data);

        return [
            'area_nivel' => $areaNivel,
            'message' => 'Relación área-nivel creada exitosamente'
        ];
    }

   public function createMultipleAreaNivel(array $data): array
    {
    $olimpiadaActual=$this->obtenerOlimpiadaActual();
    $inserted = [];
    
    foreach ($data as $relacion) {
        $existing = AreaNivel::where('id_area', $relacion['id_area'])
                        ->where('id_nivel', $relacion['id_nivel'])
                        ->where('id_olimpiada', $olimpiadaActual->id_olimpiada)
                        ->first();

        if (!$existing) {
            $inserted[] = $this->areaNivelRepository->createAreaNivel([
                'id_area' => $relacion['id_area'],
                'id_nivel' => $relacion['id_nivel'],
                'id_olimpiada' => $olimpiadaActual->id_olimpiada,
                'activo' => $relacion['activo']
            ]);
        } else {
            $inserted[] = $existing;
        }
    }

    $message = count($inserted) . ' relaciones área-nivel procesadas para la olimpiada actual';
    if (count($inserted) < count($data)) {
        $message .= ' (algunas relaciones ya existían)';
    }

    return [
        'area_niveles' => $inserted,
        'olimpiada' => $olimpiadaActual->gestion,
        'message' => $message
    ];
    }

    public function updateAreaNivelByArea(int $id_area, array $niveles): array
    {
        $olimpiadaActual = $this->obtenerOlimpiadaActual();
        $updatedNiveles = [];
    
    foreach ($niveles as $nivelData) {
        $areaNivel = AreaNivel::where('id_area', $id_area)
                        ->where('id_nivel', $nivelData['id_nivel'])
                        ->where('id_olimpiada', $olimpiadaActual->id_olimpiada)
                        ->first();

        if ($areaNivel) {
            $areaNivel->update(['activo' => $nivelData['activo']]);
            $updatedNiveles[] = $areaNivel;
        } else {
            $newAreaNivel = $this->areaNivelRepository->createAreaNivel([
                'id_area' => $id_area,
                'id_nivel' => $nivelData['id_nivel'],
                'id_olimpiada' => $olimpiadaActual->id_olimpiada,
                'activo' => $nivelData['activo']
            ]);
            $updatedNiveles[] = $newAreaNivel;
        }
    }

    return [
        'area_niveles' => $updatedNiveles,
        'olimpiada' => $olimpiadaActual->gestion,
        'message' => 'Relaciones área-nivel actualizadas exitosamente'
    ];
    }

     public function updateAreaNivel(int $id, array $data): array
    {
        $updated = $this->areaNivelRepository->update($id, $data);

        if (!$updated) {
            throw new \Exception('Relación área-nivel no encontrada');
        }

        $areaNivel = $this->areaNivelRepository->getById($id);

        return [
            'area_nivel' => $areaNivel,
            'message' => 'Relación área-nivel actualizada exitosamente'
        ];
    }

    public function deleteAreaNivel(int $id): array
    {
        $deleted = $this->areaNivelRepository->delete($id);

        if (!$deleted) {
            throw new \Exception('Relación área-nivel no encontrada');
        }

        return [
            'message' => 'Relación área-nivel eliminada exitosamente'
        ];
    }
}