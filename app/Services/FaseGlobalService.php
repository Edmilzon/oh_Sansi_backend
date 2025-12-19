<?php

namespace App\Services;

use App\Model\CronogramaFase;
use App\Model\FaseGlobal;
use App\Model\Olimpiada;
use App\Repositories\FaseGlobalRepository;
use App\Services\SistemaEstadoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

class FaseGlobalService
{
    public function __construct(
        protected FaseGlobalRepository $repository,
        protected SistemaEstadoService $sistemaEstadoService,
        protected CronogramaFaseService $cronogramaService
    ) {}

    public function crearFaseCompleta(array $data)
    {
        $olimpiada = Olimpiada::where('estado', 1)->first();

        if (!$olimpiada) {
            throw new Exception("No existe una gestión activa para crear fases.");
        }

        $this->validarColisionTemporal(
            $olimpiada->id_olimpiada,
            $data['fecha_inicio'],
            $data['fecha_fin']
        );

        return DB::transaction(function () use ($data, $olimpiada) {

            $debeActivarse = $data['activar_ahora'] ?? false;

            if ($data['orden'] == 1 && !$this->hayFasesActivas($olimpiada->id_olimpiada)) {
                $debeActivarse = true;
            }

            if ($debeActivarse) {
                $this->apagarOtrasFases($olimpiada->id_olimpiada);
            }

            $fase = $this->repository->create([
                'id_olimpiada' => $olimpiada->id_olimpiada,
                'codigo'       => $data['codigo'],
                'nombre'       => $data['nombre'],
                'orden'        => $data['orden'],
            ]);

            $cronograma = CronogramaFase::create([
                'id_fase_global' => $fase->id_fase_global,
                'fecha_inicio'   => $data['fecha_inicio'],
                'fecha_fin'      => $data['fecha_fin'],
                'estado'         => $debeActivarse ? 1 : 0
            ]);

            if ($debeActivarse) {
                $this->sistemaEstadoService->difundirCambioDeEstado();
            }

            return ['fase' => $fase, 'cronograma' => $cronograma];
        });
    }

    private function validarColisionTemporal($idOlimpiada, $inicio, $fin)
    {
        $existeCruce = CronogramaFase::query()
            ->whereHas('faseGlobal', function ($q) use ($idOlimpiada) {
                $q->where('id_olimpiada', $idOlimpiada);
            })
            ->where(function ($q) use ($inicio, $fin) {
                $q->where('fecha_inicio', '<', $fin)
                  ->where('fecha_fin', '>', $inicio);
            })
            ->exists();

        if ($existeCruce) {
            throw ValidationException::withMessages([
                'fecha_inicio' => ['El rango de fechas seleccionado choca con otra fase ya existente (Verifique el cronograma).']
            ]);
        }
    }

    private function apagarOtrasFases($idOlimpiada)
    {
        CronogramaFase::whereHas('faseGlobal', function ($q) use ($idOlimpiada) {
            $q->where('id_olimpiada', $idOlimpiada);
        })->update(['estado' => 0]);
    }

    private function hayFasesActivas($idOlimpiada): bool
    {
        return CronogramaFase::whereHas('faseGlobal', function ($q) use ($idOlimpiada) {
            $q->where('id_olimpiada', $idOlimpiada);
        })->where('estado', 1)->exists();
    }

    public function listarFasesActuales()
    {
        $olimpiada = Olimpiada::where('estado', 1)->first();

        if (!$olimpiada) {
            return collect([]);
        }
        return $this->repository->getByOlimpiada($olimpiada->id_olimpiada);
    }

    public function obtenerDetalleFase(int $id)
    {
        return $this->repository->find($id);
    }

    /**
     * Delega la actualización del cronograma al servicio correspondiente.
     */
    public function actualizarCronograma(int $idFaseGlobal, array $data)
    {
        return DB::transaction(function () use ($idFaseGlobal, $data) {
            // Delegamos la lógica compleja al servicio de Cronograma
            return $this->cronogramaService->actualizarCronogramaDeFase($idFaseGlobal, $data);
        });
    }
}
