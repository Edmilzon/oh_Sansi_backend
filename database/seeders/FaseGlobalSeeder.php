<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\FaseGlobal;
use App\Model\Olimpiada;
use App\Model\CronogramaFase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FaseGlobalSeeder extends Seeder
{
    public function run(): void
    {
        $olimpiada = Olimpiada::updateOrCreate(
            [
                'nombre'  => 'Olimpíada Tu Buena Vida',
                'gestion' => '2025'
            ],
            [
                'estado' => 1
            ]
        );

        $this->command->info("Olimpiada gestionada: {$olimpiada->nombre} (ID: {$olimpiada->id_olimpiada})");

        $fases = [
            [
                'nombre' => 'Fase de Configuración',
                'codigo' => 'CONFIGURACION',
                'orden'  => 1,
                'inicio_relativo' => 0,
                'fin_relativo'    => 15,
                'es_activa'       => 1
            ],
            [
                'nombre' => '1ra Etapa - Clasificatoria',
                'codigo' => 'EVALUACION',
                'orden'  => 2,
                'inicio_relativo' => 20,
                'fin_relativo'    => 30,
                'es_activa'       => 0
            ],
            [
                'nombre' => 'Etapa Final Departamental',
                'codigo' => 'FINAL',
                'orden'  => 3,
                'inicio_relativo' => 40,
                'fin_relativo'    => 50,
                'es_activa'       => 0
            ],
        ];

        foreach ($fases as $datosFase) {

            $fase = FaseGlobal::updateOrCreate(
                [
                    'codigo'       => $datosFase['codigo'],
                    'id_olimpiada' => $olimpiada->id_olimpiada
                ],
                [
                    'nombre' => $datosFase['nombre'],
                    'orden'  => $datosFase['orden'],
                ]
            );

            $fechaInicio = Carbon::now()->addDays($datosFase['inicio_relativo'])->setTime(8, 0, 0);
            $fechaFin    = Carbon::now()->addDays($datosFase['fin_relativo'])->setTime(23, 59, 59);

            CronogramaFase::updateOrCreate(
                [
                    'id_fase_global' => $fase->id_fase_global
                ],
                [
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin'    => $fechaFin,
                    'estado'       => $datosFase['es_activa']
                ]
            );
        }

        $this->command->info('Fases globales y sus cronogramas han sido creados exitosamente.');
    }
}
