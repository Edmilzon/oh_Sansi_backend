<?php

namespace App\Services;

use App\Model\Examen;
use App\Model\Parametro;
use Illuminate\Support\Facades\Log;

class CalculadoraResultadosService
{
    /**
     * Punto de entrada: Ejecuta el cálculo automático al cerrar un examen.
     * * @param Examen $examen
     */
    public function procesarResultados(Examen $examen): void
    {
        if ($examen->evaluaciones()->doesntExist()) {
            return;
        }

        match ($examen->tipo_regla) {
            'nota_corte' => $this->aplicarNotaCorte($examen),
            default      => $this->logicaPorDefecto($examen),
        };
    }

    /**
     * ESTRATEGIA: Nota de Corte (Filtro).
     * Determina si el estudiante "CLASIFICADO" o "NO CLASIFICADO" en este examen.
     */
    private function aplicarNotaCorte(Examen $examen): void
    {
        $notaMinima = $this->obtenerNotaMinima($examen);

        foreach ($examen->evaluaciones as $evaluacion) {

            if ($evaluacion->estado_participacion !== 'presente') {
                $estado = match($evaluacion->estado_participacion) {
                    'ausente' => 'REPROBADO (Ausente)',
                    'descalificado_etica' => 'DESCALIFICADO',
                    default => 'NO CLASIFICADO'
                };

                $evaluacion->update(['resultado_calculado' => $estado]);
                continue;
            }

            $resultado = ($evaluacion->nota >= $notaMinima) ? 'CLASIFICADO' : 'NO CLASIFICADO';

            $evaluacion->update(['resultado_calculado' => $resultado]);
        }
    }

    /**
     * Lógica por defecto para exámenes sumativos (sin regla de corte).
     * Solo limpia el campo de resultado.
     */
    private function logicaPorDefecto(Examen $examen): void
    {
        foreach ($examen->evaluaciones as $evaluacion) {
            if ($evaluacion->estado_participacion === 'presente') {
                $evaluacion->update(['resultado_calculado' => 'COMPLETADO']);
            }
        }
    }

    /**
     * Helper: Obtiene la nota mínima priorizando la configuración local (JSON)
     * y cayendo en la configuración global (Tabla Parametro) si no existe.
     */
    private function obtenerNotaMinima(Examen $examen): float
    {
        $config = $examen->configuracion_reglas;

        if (isset($config['nota_minima']) && is_numeric($config['nota_minima'])) {
            return (float) $config['nota_minima'];
        }

        $competencia = $examen->competencia;

        if ($competencia && $competencia->id_area_nivel) {
            $parametro = Parametro::where('id_area_nivel', $competencia->id_area_nivel)->first();

            if ($parametro && !is_null($parametro->nota_min_aprobacion)) {
                return (float) $parametro->nota_min_aprobacion;
            }
        }

        return 51.0;
    }
}
