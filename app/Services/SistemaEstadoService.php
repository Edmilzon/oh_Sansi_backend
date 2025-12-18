<?php

namespace App\Services;

use App\Model\Olimpiada;
use App\Events\SistemaEstadoActualizado;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SistemaEstadoService
{
    /**
     * Obtiene la fotografía del estado actual del sistema.
     */
    public function obtenerSnapshotDelSistema(): array
    {
        // 1. Obtener Gestión Actual (Olimpiada Activa)
        $gestion = Olimpiada::where('estado', 1)->first();

        if (!$gestion) {
            return [
                'status' => 'sin_gestion',
                'mensaje' => 'No hay ninguna olimpiada activa configurada.',
                'server_timestamp' => now()->toIso8601String(),
                'gestion_actual' => null,
                'fase_global_activa' => null,
                'cronograma_vigente' => null,
            ];
        }

        // 2. Obtener la Fase Actual (La que tiene cronograma activo)
        $faseActiva = DB::table('fase_global as fg')
            ->join('cronograma_fase as cf', 'fg.id_fase_global', '=', 'cf.id_fase_global')
            ->where('fg.id_olimpiada', $gestion->id_olimpiada)
            ->where('cf.estado', 1)
            ->select([
                'fg.id_fase_global',
                'fg.codigo',
                'fg.nombre as nombre_fase',
                'fg.orden',
                'cf.fecha_inicio',
                'cf.fecha_fin',
                'cf.id_cronograma_fase'
            ])
            ->first();

        // 3. Calcular Estado del Cronograma (Tiempo Real)
        $cronogramaInfo = null;
        if ($faseActiva) {
            $ahora = Carbon::now();
            $inicio = Carbon::parse($faseActiva->fecha_inicio);
            $fin = Carbon::parse($faseActiva->fecha_fin);

            $cronogramaInfo = [
                'fecha_inicio' => $inicio->toIso8601String(),
                'fecha_fin' => $fin->toIso8601String(),
                'en_fecha' => $ahora->between($inicio, $fin),
                'dias_restantes' => $ahora->diffInDays($fin, false),
                'mensaje' => $this->generarMensajeTiempo($ahora, $inicio, $fin)
            ];
        }

        return [
            'status' => 'operativo',
            'server_timestamp' => now()->toIso8601String(),
            'gestion_actual' => [
                'id' => $gestion->id_olimpiada,
                'nombre' => $gestion->nombre,
                'gestion' => $gestion->gestion,
            ],
            'fase_global_activa' => $faseActiva ? [
                'id' => $faseActiva->id_fase_global,
                'codigo' => $faseActiva->codigo,
                'nombre' => $faseActiva->nombre_fase,
                'orden' => $faseActiva->orden
            ] : null,
            'cronograma_vigente' => $cronogramaInfo
        ];
    }

    /**
     * Difunde el estado actual a todos los clientes conectados.
     */
    public function difundirCambioDeEstado(): void
    {
        $snapshot = $this->obtenerSnapshotDelSistema();
        SistemaEstadoActualizado::dispatch($snapshot);
    }

    private function generarMensajeTiempo($ahora, $inicio, $fin): string
    {
        if ($ahora->lt($inicio)) return "Inicia " . $inicio->diffForHumans($ahora);
        if ($ahora->gt($fin)) return "Finalizó " . $fin->diffForHumans($ahora);
        return "En curso";
    }
}
