<?php

namespace App\Services;

use App\Repositories\CronogramaFaseRepository;
use App\Model\CronogramaFase;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\OlimpiadaRepository;
use Illuminate\Validation\ValidationException;

class CronogramaFaseService
{
    public function __construct(
        protected CronogramaFaseRepository $repository,
        protected OlimpiadaRepository $olimpiadaRepository,
        protected SistemaEstadoService $sistemaEstadoService
    ) {}

    public function listarTodos(): Collection
    {
        return $this->repository->getAll();
    }

    public function crear(array $data): CronogramaFase
    {
        return $this->repository->create($data);
    }

    public function obtenerPorId(int $id): CronogramaFase
    {
        return $this->repository->find($id);
    }

    public function actualizar(int $id, array $data): CronogramaFase
    {
        return $this->repository->update($id, $data);
    }

    public function eliminar(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function listarVigentes(): Collection
    {
        $olimpiadaActual = $this->olimpiadaRepository->obtenerMasReciente();

        if (!$olimpiadaActual) {
            return new Collection();
        }

        return $this->repository->obtenerPorOlimpiada($olimpiadaActual->id_olimpiada);
    }

    public function actualizarCronogramaDeFase(int $idFaseGlobal, array $data): CronogramaFase
    {
        $cronograma = CronogramaFase::where('id_fase_global', $idFaseGlobal)->firstOrFail();

        if (isset($data['fecha_inicio']) || isset($data['fecha_fin'])) {
            $inicio = $data['fecha_inicio'] ?? $cronograma->fecha_inicio;
            $fin = $data['fecha_fin'] ?? $cronograma->fecha_fin;
            $idOlimpiada = $cronograma->faseGlobal->id_olimpiada;

            $this->validarColisionTemporal($idOlimpiada, $inicio, $fin, $cronograma->id_cronograma_fase);
        }

        $cronograma->update($data);

        if (isset($data['estado']) && $data['estado'] == 1) {
            $this->apagarOtrosCronogramas($cronograma);
            $this->sistemaEstadoService->difundirCambioDeEstado();
        }

        return $cronograma;
    }

    private function validarColisionTemporal($idOlimpiada, $inicio, $fin, $exceptId = null)
    {
        $query = CronogramaFase::query()
            ->whereHas('faseGlobal', function ($q) use ($idOlimpiada) {
                $q->where('id_olimpiada', $idOlimpiada);
            })
            ->where(function ($q) use ($inicio, $fin) {
                $q->where('fecha_inicio', '<', $fin)
                  ->where('fecha_fin', '>', $inicio);
            });

        if ($exceptId) {
            $query->where('id_cronograma_fase', '!=', $exceptId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'fecha_inicio' => ['El rango de fechas choca con otra fase existente en el cronograma.']
            ]);
        }
    }

    private function apagarOtrosCronogramas(CronogramaFase $cronogramaActual)
    {
        $idOlimpiada = $cronogramaActual->faseGlobal->id_olimpiada;

        CronogramaFase::whereHas('faseGlobal', function ($q) use ($idOlimpiada) {
                $q->where('id_olimpiada', $idOlimpiada);
            })
            ->where('id_cronograma_fase', '!=', $cronogramaActual->id_cronograma_fase)
            ->update(['estado' => 0]);
    }
}
