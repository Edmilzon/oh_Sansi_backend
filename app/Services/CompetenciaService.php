<?php

namespace App\Services;

use App\Repositories\CompetenciaRepository;
use App\Model\Competencia;
use App\Repositories\UsuarioRepository;
use App\Events\CompetenciaEstadoCambiado;
use Exception;

class CompetenciaService
{
    public function __construct(
        protected CompetenciaRepository $repository,
        protected UsuarioRepository $usuarioRepo
    ) {}

    /**
     * Crea una competencia en estado 'borrador'.
     */
    public function crear(array $data): Competencia
    {
        return $this->repository->create($data);
    }

    /**
     * Actualiza la configuración. Solo si está en 'borrador'.
     */
    public function actualizar(int $id, array $data): Competencia
    {
        $competencia = $this->repository->find($id);

        if ($competencia->estado_fase !== 'borrador') {
            throw new Exception("Solo se pueden editar competencias en estado borrador. La competencia actual está en: {$competencia->estado_fase}");
        }

        $this->repository->update($data, $id);
        return $this->repository->find($id);
    }

    /**
     * Elimina la competencia. Solo si está en 'borrador'.
     */
    public function eliminar(int $id): void
    {
        $competencia = $this->repository->find($id);

        if ($competencia->estado_fase !== 'borrador') {
            throw new Exception("No se puede eliminar una competencia activa o finalizada.");
        }

        $this->repository->delete($id);
    }

    /**
     * Paso 1 del Ciclo de Vida: Publicar (Hacer visible).
     * Regla de Negocio: Valida integridad matemática antes de exponer.
     */
    public function publicar(int $id): Competencia
    {
        $competencia = $this->repository->find($id);

        if ($competencia->estado_fase !== 'borrador') {
            throw new Exception("La competencia ya no está en borrador.");
        }

        if ($competencia->examenes()->count() === 0) {
            throw new Exception("No puedes publicar una competencia sin exámenes configurados.");
        }

        if ($competencia->criterio_clasificacion === 'suma_ponderada') {
            $suma = $competencia->examenes()->sum('ponderacion');
            if (round($suma, 2) != 100.00) {
                throw new Exception("Error de configuración: La suma de las ponderaciones de los exámenes debe ser exactamente 100%. Suma actual: {$suma}%.");
            }
        }

        $competencia->update(['estado_fase' => 'publicada']);

        broadcast(new CompetenciaEstadoCambiado($competencia, 'publicada'))->toOthers();
        return $competencia;
    }

    /**
     * Paso 2 del Ciclo de Vida: Iniciar (Fase Operativa).
     * Habilita a los responsables para abrir las mesas de examen.
     */
    public function iniciar(int $id): Competencia
    {
        $competencia = $this->repository->find($id);

        if ($competencia->estado_fase !== 'publicada') {
            throw new Exception("La competencia debe estar en estado 'publicada' para poder iniciarse.");
        }

        $competencia->update(['estado_fase' => 'en_proceso']);

        broadcast(new CompetenciaEstadoCambiado($competencia, 'publicada'))->toOthers();
        return $competencia;
    }

    public function listarPorResponsableYArea(int $idUsuario, int $idArea)
    {
        $esResponsable = $this->usuarioRepo->tieneRol($idUsuario, 'Responsable de Area');

        if (!$esResponsable) {
            throw new Exception("El usuario no tiene el rol de 'Responsable de Area' o no existe.", 403);
        }

        return $this->repository->getByResponsableAndArea($idUsuario, $idArea);
    }

    public function listarNivelesPorArea(int $idArea)
    {
        return $this->repository->getNivelesPorAreaActual($idArea);
    }

    public function agruparAreasNivelesPorResponsable(int $idResponsable): array
    {
        // 1. Obtener datos crudos
        $competencias = $this->repository->getActivasPorResponsable($idResponsable);

        if ($competencias->isEmpty()) {
            return [];
        }

        // 2. Agrupar por ID de Área
        $agrupado = $competencias->groupBy(function ($comp) {
            return $comp->areaNivel->areaOlimpiada->area->id_area;
        });

        $resultado = [];

        // 3. Construir la estructura jerárquica
        foreach ($agrupado as $idArea => $items) {
            $primero = $items->first(); // Tomamos datos del área del primer elemento

            // Mapeamos los niveles (evitando duplicados si hubiera 2 competencias del mismo nivel)
            $niveles = $items->map(function ($comp) {
                return [
                    'id_area_nivel' => $comp->id_area_nivel,
                    'id_nivel'      => $comp->areaNivel->nivel->id_nivel,
                    'nombre_nivel'  => $comp->areaNivel->nivel->nombre,
                    // Extra útil: Mandamos el ID de la competencia por si el front lo necesita
                    'id_competencia' => $comp->id_competencia
                ];
            })->unique('id_area_nivel')->values();

            $resultado[] = [
                'id_Area' => $idArea,
                'área'    => $primero->areaNivel->areaOlimpiada->area->nombre,
                'niveles' => $niveles
            ];
        }

        return $resultado;
    }
}
