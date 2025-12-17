<?php

namespace App\Services;

use App\Events\SistemaEstadoActualizado;
use App\Model\Olimpiada;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SistemaEstadoService
{
    /**
     * Construye la fotografía actual del sistema.
     */
    public function obtenerSnapshotDelSistema(): array
    {
        // 1. Gestión Actual (Olimpiada con estado=1)
        $gestion = Olimpiada::where('estado', 1)->first();

        if (!$gestion) {
            return [
                'status' => 'sin_gestion',
                'mensaje' => 'No hay olimpiada activa.',
                'server_timestamp' => now()->toIso8601String(),
                'gestion_actual' => null,
                'fase_global_activa' => null,
                'cronograma_vigente' => null,
            ];
        }

        // 2. Fase Activa (Inferida por Cronograma Activo)
        $faseActiva = DB::table('fase_global as fg')
            ->join('cronograma_fase as cf', 'fg.id_fase_global', '=', 'cf.id_fase_global')
            ->where('fg.id_olimpiada', $gestion->id_olimpiada)
            ->where('cf.estado', 1) // El interruptor maestro
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

        // 3. Cálculo de Tiempos
        $cronogramaInfo = null;
        if ($faseActiva) {
            $ahora = Carbon::now();
            $inicio = Carbon::parse($faseActiva->fecha_inicio);
            $fin = Carbon::parse($faseActiva->fecha_fin); // Precisión datetime

            $cronogramaInfo = [
                'fecha_inicio' => $inicio->toIso8601String(),
                'fecha_fin' => $fin->toIso8601String(),
                'en_fecha' => $ahora->between($inicio, $fin),
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
     * Método para llamar cuando el Admin cambia algo.
     * Fuerza la actualización en todos los clientes conectados.
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
