<?php

namespace App\Services;

use App\Repositories\ConfiguracionAccionRepository;
use App\Model\AccionSistema;
use App\Model\FaseGlobal;
use App\Model\Olimpiada;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ConfiguracionAccionService
{
    public function __construct(
        protected ConfiguracionAccionRepository $repoConfig
    ) {}

    public function obtenerMatrizCompleta(): array
    {
        $olimpiada = Olimpiada::where('estado', '1')->first();
        if (!$olimpiada) {
            throw new Exception("No hay una olimpiada activa configurada en el sistema.");
        }

        $fases = FaseGlobal::where('id_olimpiada', $olimpiada->id_olimpiada)
            ->orderBy('orden', 'asc')
            ->get();

        if ($fases->isEmpty()) return [];

        $this->sincronizarFaltantes($fases);

        $faseIds = $fases->pluck('id_fase_global')->toArray();
        $registros = $this->repoConfig->getByFases($faseIds);

        return $registros->groupBy('id_fase_global')
            ->map(function ($items) {
                $faseInfo = $items->first()->faseGlobal;
                return [
                    'fase' => [
                        'id'     => $faseInfo->id_fase_global,
                        'nombre' => $faseInfo->nombre,
                        'codigo' => $faseInfo->codigo,
                        'orden'  => $faseInfo->orden,
                    ],
                    'acciones' => $items->map(function ($item) {
                        return [
                            'id_configuracion_accion' => $item->id_configuracion_accion,
                            'id_accion_sistema'       => $item->id_accion_sistema,
                            'codigo'                  => $item->accionSistema->codigo,
                            'nombre_accion'           => $item->accionSistema->nombre,
                            'habilitada'              => (bool) $item->habilitada,
                        ];
                    })->values()->toArray()
                ];
            })
            ->values()
            ->toArray();
    }

    public function actualizarConfiguracion(array $datos): void
    {
        $listaCambios = $datos['configuraciones'] ?? [];
        if (empty($listaCambios) && isset($datos[0])) {
            $listaCambios = $datos;
        }

        if (empty($listaCambios)) {
            Log::warning('ConfiguracionAccionService: Lista vacía, no se actualizó nada.');
            return;
        }

        DB::transaction(function () use ($listaCambios) {
            foreach ($listaCambios as $config) {
                if (isset($config['id_configuracion_accion'])) {
                    $estado = filter_var($config['habilitada'], FILTER_VALIDATE_BOOLEAN);

                    $this->repoConfig->updateStatus(
                        $config['id_configuracion_accion'],
                        $estado
                    );
                }
            }
        });
    }

    private function sincronizarFaltantes($fases): void
    {
        $acciones = AccionSistema::all();

        if ($acciones->isEmpty() || $fases->isEmpty()) return;

        DB::transaction(function () use ($acciones, $fases) {
            foreach ($fases as $fase) {
                foreach ($acciones as $accion) {
                    $this->repoConfig->firstOrCreate(
                        [
                            'id_accion_sistema' => $accion->id_accion_sistema,
                            'id_fase_global'    => $fase->id_fase_global,
                        ],
                        [
                            'habilitada' => false
                        ]
                    );
                }
            }
        });
    }
}
