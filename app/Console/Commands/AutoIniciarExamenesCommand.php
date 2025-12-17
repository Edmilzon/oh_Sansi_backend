<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\Examen;
use App\Services\ExamenService;
use Illuminate\Support\Facades\Log;

class AutoIniciarExamenesCommand extends Command
{
    /**
     * El nombre y la firma del comando en consola.
     * Uso: php artisan examenes:auto-iniciar
     */
    protected $signature = 'examenes:auto-iniciar';

    protected $description = 'Busca exámenes programados para esta hora y los inicia automáticamente.';

    public function __construct(
        protected ExamenService $examenService
    ) {
        parent::__construct();
    }

    public function handle(): void
    {

        $examenesParaIniciar = Examen::where('estado_ejecucion', 'no_iniciada')
            ->where('fecha_hora_inicio', '<=', now())
            ->whereHas('competencia', function ($q) {
                $q->where('estado_fase', 'en_proceso');
            })
            ->get();

        if ($examenesParaIniciar->isEmpty()) {
            $this->info('No hay exámenes para iniciar en este momento.');
            return;
        }

        foreach ($examenesParaIniciar as $examen) {
            try {
                $this->info("Iniciando examen automáticamente: ID {$examen->id_examen} - {$examen->nombre}");
                $this->examenService->iniciarExamen($examen->id_examen);

                Log::info("AUTO-START: Examen ID {$examen->id_examen} iniciado por el sistema.");
            } catch (\Exception $e) {
                Log::error("AUTO-START ERROR: No se pudo iniciar el examen ID {$examen->id_examen}. Error: {$e->getMessage()}");
            }
        }

        $this->info('Proceso completado.');
    }
}
