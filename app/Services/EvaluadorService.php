<?php

namespace App\Services;

use App\Repositories\EvaluadorRepository;
use App\Model\Usuario;
use App\Model\ResponsableArea;
use App\Model\Area;
use App\Model\EvaluadorAn;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EvaluadorService
{
    protected $evaluadorRepository;

    public function __construct(EvaluadorRepository $evaluadorRepository)
    {
        $this->evaluadorRepository = $evaluadorRepository;
    }

    /**
     * Crea un nuevo responsable de área.
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function createEvaluador(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // Crear el usuario
            $usuario = $this->evaluadorRepository->createUsuario($data);

            // Asignar rol de "Evaluador"
            $this->evaluadorRepository->assignEvaluadorRole($usuario, $data['id_olimpiada']);

            // Crear relaciones con las áreas
            $evaluadorAreas = $this->evaluadorRepository->createEvaluadorAreaRelations(
                $usuario,
                $data['areas'],
                $data['id_olimpiada']
            );

            // Obtener información completa del evaluador creado
            return $this->getEvaluadorData($usuario, $evaluadorAreas);
        });
    }

    /**
     * Obtiene todos los evaluadores.
     *
     * @return array
     */
    public function getAllEvaluadores(): array
    {
        return $this->evaluadorRepository->getAllEvaluadoresWithAreas();
    }

    /**
     * Obtiene un evaluador específico por ID.
     *
     * @param int $id
     * @return array|null
     */
    public function getEvaluadorById(int $id): ?array
    {
        return $this->evaluadorRepository->getEvaluadorByIdWithAreas($id);
    }

    /**
     * Obtiene responsables por área específica.
     *
     * @param int $areaId
     * @return array
     */
    public function getEvaluadoresByArea(int $areaId): array
    {
        return $this->evaluadorRepository->getEvaluadoresByArea($areaId);
    }

    /**
     * Obtiene responsables por olimpiada específica.
     *
     * @param int $olimpiadaId
     * @return array
     */
    public function getEvaluadoresByOlimpiada(int $olimpiadaId): array
    {
        return $this->evaluadorRepository->getEvaluadoresByOlimpiada($olimpiadaId);
    }

    /**
     * Actualiza un evaluador existente.
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function updateEvaluador(int $id, array $data): array
    {
        return DB::transaction(function () use ($id, $data) {
            $usuario = $this->evaluadorRepository->updateUsuario($id, $data);

            if (isset($data['areas'])) {
                $this->evaluadorRepository->updateEvaluadorAreaRelations($usuario, $data['areas']);
            }

            return $this->getEvaluadorData($usuario);
        });
    }

    /**
     * Elimina un evaluador.
     *
     * @param int $id
     * @return bool
     */
    public function deleteEvaluador(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return $this->evaluadorRepository->deleteEvaluador($id);
        });
    }

    /**
     * Valida que las áreas existan.
     *
     * @param array $areaIds
     * @return void
     * @throws ValidationException
     */
    public function validateAreas(array $areaIds): void
    {
        $existingAreas = Area::whereIn('id_area', $areaIds)->pluck('id_area')->toArray();
        $missingAreas = array_diff($areaIds, $existingAreas);

        if (!empty($missingAreas)) {
            throw ValidationException::withMessages([
                'areas' => ['Las siguientes áreas no existen: ' . implode(', ', $missingAreas)]
            ]);
        }
    }

    /**
     * Obtiene los datos formateados del responsable.
     *
     * @param Usuario $usuario
     * @param array|null $responsableAreas
     * @return array
     */
    private function getEvaluadorData(Usuario $usuario, ?array $evaluadorAreas = null): array
    {
        if (!$evaluadorAreas) {
            $evaluadorAreas = $usuario->evaluadorArea()->with('area')->get()->toArray();
        }

        return [
            'id_usuario' => $usuario->id_usuario,
            'nombre' => $usuario->nombre,
            'apellido' => $usuario->apellido,
            'ci' => $usuario->ci,
            'email' => $usuario->email,
            'telefono' => $usuario->telefono,
            'rol' => 'Evaluador',
            'areas_asignadas' => array_map(function ($ra) {
                return [
                    'id_area' => $ra['area']['id_area'],
                    'nombre_area' => $ra['area']['nombre']
                ];
            }, $evaluadorAreas),
            'created_at' => $usuario->created_at,
            'updated_at' => $usuario->updated_at
        ];
    }
}
