<?php

namespace App\Services;

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