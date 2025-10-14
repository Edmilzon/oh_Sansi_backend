<?php

namespace App\Services;
use App\Models\AreaNivel;
use App\Repositories\AreaNivelRepository;
use Illuminate\Database\Eloquent\Collection;

class AreaNivelService {
    protected $areaNivelRepository;

    public function __construct(AreaNivelRepository $areaNivelRepository){
        $this->areaNivelRepository = $areaNivelRepository;
    }

    public function getAreaNivelList(){
        return $this->areaNivelRepository->getAllAreasNiveles();
    }

    public function getAreaNivelByArea(int $id_area){
         $areaNiveles = $this->areaNivelRepository->getByArea($id_area);

         return $areaNiveles->map(function($areaNivel) {
            return [
                'id_area_nivel' => $areaNivel->id_area_nivel,
                'id_area' => $areaNivel->id_area,
                'id_nivel' => $areaNivel->id_nivel,
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
        return $this->areaNivelRepository->getByAreaAll($id_area);
    }

    public function getAreaNivelesAsignadosAll(): array
    {
    $areas = $this->areaNivelRepository->getAreaNivelAsignadosAll();
    
    $resultado = $areas->filter(function($area) {
        // Si no tiene relaciones en area_nivel
        if ($area->areaNiveles->isEmpty()) {
            return true;
        }
        
        // Si tiene relaciones, solo se muestra si al menos una tiene activo = true
        return $area->areaNiveles->contains('activo', true);
    })->map(function($area) {
        
        // Si no tiene relaciones, array vacío
        if ($area->areaNiveles->isEmpty()) {
            return [
                'id_area' => $area->id_area,
                'nombre' => $area->nombre,
                'activo' => (bool)$area->activo,
                'niveles' => []
            ];
        }
        
        // Si tiene relaciones, mostrar solo los niveles con activo = true
        $nivelesArray = $area->areaNiveles->filter(function($areaNivel) {
            return $areaNivel->activo === true;
        })->map(function($areaNivel) {
            return [
                'id_nivel' => $areaNivel->nivel->id_nivel,
                'nombre' => $areaNivel->nivel->nombre,
                'orden' => $areaNivel->nivel->orden,
                'asignado_activo' => $areaNivel->activo
            ];
        });
        
        return [
            'id_area' => $area->id_area,
            'nombre' => $area->nombre,
            'activo' => (bool)$area->activo,
            'niveles' => $nivelesArray->values()
        ];
        });
    
    return [
        'areas' => $resultado->values(),
        'message' => 'Se muestran las áreas que tienen al menos una relación activa o no tienen relaciones'
    ];
    }

    public function createNewAreaNivel(array $data){
        $existing = AreaNivel::where('id_area', $data['id_area'])
                            ->where('id_nivel', $data['id_nivel'])
                            ->first();

        if ($existing) {
            throw new \Exception('Ya existe este nivel asociado a esta área.');
        }

        $areaNivel = $this->areaNivelRepository->create($data);

        return [
            'area_nivel' => $areaNivel,
            'message' => 'Relación área-nivel creada exitosamente'
        ];
    }

   public function createMultipleAreaNivel(array $data): array
    {
    $inserted = [];
    
    foreach ($data as $relacion) {
        $existing = AreaNivel::where('id_area', $relacion['id_area'])
                        ->where('id_nivel', $relacion['id_nivel'])
                        ->first();

        if (!$existing) {
            $inserted[] = $this->areaNivelRepository->createAreaNivel([
                'id_area' => $relacion['id_area'],
                'id_nivel' => $relacion['id_nivel'],
                'activo' => $relacion['activo']
            ]);
        } else {
            $inserted[] = $existing;
        }
    }

    $message = count($inserted) . ' relaciones área-nivel procesadas';
    if (count($inserted) < count($data)) {
        $message .= ' (algunas relaciones ya existían)';
    }

    return [
        'area_niveles' => $inserted,
        'message' => $message
    ];
    }

    public function updateAreaNivelByArea(int $id_area, array $niveles): array
    {
    $updatedNiveles = [];
    
    foreach ($niveles as $nivelData) {
        $areaNivel = AreaNivel::where('id_area', $id_area)
                        ->where('id_nivel', $nivelData['id_nivel'])
                        ->first();

        if ($areaNivel) {
            // Actualizar existente
            $areaNivel->update(['activo' => $nivelData['activo']]);
            $updatedNiveles[] = $areaNivel;
        } else {
            // Crear nuevo si no existe
            $newAreaNivel = $this->areaNivelRepository->createAreaNivel([
                'id_area' => $id_area,
                'id_nivel' => $nivelData['id_nivel'],
                'activo' => $nivelData['activo']
            ]);
            $updatedNiveles[] = $newAreaNivel;
        }
    }

    return [
        'area_niveles' => $updatedNiveles,
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