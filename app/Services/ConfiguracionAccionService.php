<?php

namespace App\Services;

use App\Repositories\ConfiguracionAccionRepository;
use App\Services\UsuarioAccionesService;
use App\Events\MisAccionesActualizadas;
use App\Model\AccionSistema;
use App\Model\FaseGlobal;
use App\Model\Olimpiada;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ConfiguracionAccionService
{
    public function __construct(
        protected ConfiguracionAccionRepository $repoConfig,
        protected UsuarioAccionesService $usuarioAccionesService
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
                        'estado' => $faseInfo->estado,
                    ],
                    'acciones' => $items->map(function ($conf) {
                        return [
                            'id_configuracion_accion' => $conf->id_configuracion_accion,
                            'id_accion_sistema'       => $conf->id_accion_sistema,
                            'nombre_accion'           => $conf->accionSistema->nombre,
                            'codigo_accion'           => $conf->accionSistema->codigo,
                            'descripcion'             => $conf->accionSistema->descripcion,
                            'habilitada'              => (bool) $conf->habilitada,
                        ];
                    })->values()->toArray()
                ];
            })->values()->toArray();
    }

    public function update(array $datos)
    {
        $listaCambios = $datos;
        if (empty($listaCambios) && isset($datos[0])) {
            $listaCambios = $datos;
        }

        if (empty($listaCambios)) {
            Log::warning('ConfiguracionAccionService: Lista vacía, no se actualizó nada.');
            return;
        }

        DB::transaction(function () use ($listaCambios) {

            $accionesAfectadasIds = [];

            foreach ($listaCambios as $config) {
                if (isset($config['id_configuracion_accion'])) {

                    $estado = filter_var($config['habilitada'], FILTER_VALIDATE_BOOLEAN);

                    $registroActualizado = $this->repoConfig->updateStatus(
                        $config['id_configuracion_accion'],
                        $estado
                    );

                    if ($registroActualizado) {
                        $accionesAfectadasIds[] = $registroActualizado->id_accion_sistema;
                    }
                }
            }

            if (!empty($accionesAfectadasIds)) {
                $this->notificarUsuariosAfectados(array_unique($accionesAfectadasIds));
            }
        });
    }

    protected function notificarUsuariosAfectados(array $accionesIds)
    {
        $rolesIds = DB::table('rol_accion')
            ->whereIn('id_accion_sistema', $accionesIds)
            ->where('activo', true)
            ->pluck('id_rol')
            ->unique();

        if ($rolesIds->isEmpty()) {
            return;
        }

        $userIds = DB::table('usuario_rol')
            ->join('usuario', 'usuario.id_usuario', '=', 'usuario_rol.id_usuario')
            ->whereIn('usuario_rol.id_rol', $rolesIds)
            ->where('usuario_rol.estado', 'AC')
            ->where('usuario.estado', 'AC')
            ->pluck('usuario.id_usuario')
            ->unique();

        foreach ($userIds as $userId) {
            try {
                $nuevasAcciones = $this->usuarioAccionesService->misAcciones($userId);

                broadcast(new MisAccionesActualizadas($userId, $nuevasAcciones));

            } catch (Exception $e) {
                Log::error("Error enviando socket en ConfiguracionAccionService para usuario {$userId}: " . $e->getMessage());
            }
        }
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
