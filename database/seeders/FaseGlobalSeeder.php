<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\FaseGlobal;
use App\Model\Olimpiada;

class FaseGlobalSeeder extends Seeder
{
    public function run(): void
    {
        $olimpiada = Olimpiada::where('estado', 1)->latest('id_olimpiada')->first()
                    ?? Olimpiada::latest('id_olimpiada')->first();

        if (!$olimpiada) {
            $this->command->warn('No se encontró una olimpiada base. Ejecuta DemoCompetenciaSeeder o crea una olimpiada primero.');
            return;
        }

        $this->command->info("Creando fases para la olimpiada: {$olimpiada->nombre}");

        $fases = [
            [
                'nombre' => 'Fase de Configuración',
                'codigo' => 'CONFIG',
                'orden'  => 0,
            ],
            [
                'nombre' => '1ra Etapa - Clasificatoria',
                'codigo' => 'CLASIF',
                'orden'  => 1,
            ],
            [
                'nombre' => 'Etapa Final Departamental',
                'codigo' => 'FINAL',
                'orden'  => 2,
            ],
        ];

        foreach ($fases as $fase) {
            FaseGlobal::firstOrCreate(
                [
                    'codigo'       => $fase['codigo'],
                    'id_olimpiada' => $olimpiada->id_olimpiada
                ],
                [
                    'nombre' => $fase['nombre'],
                    'orden'  => $fase['orden'],
                ]
            );
        }

        $this->command->info('Fases globales (CONFIG, CLASIF, FINAL) verificadas.');
    }
}
