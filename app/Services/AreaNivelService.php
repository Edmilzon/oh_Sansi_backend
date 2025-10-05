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
        return $this->areaNivelRepository->getByArea($id_area);
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