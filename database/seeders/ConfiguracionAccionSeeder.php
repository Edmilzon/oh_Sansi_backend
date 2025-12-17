<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Olimpiada;
use App\Model\FaseGlobal;
use App\Model\AccionSistema;
use App\Model\ConfiguracionAccion;

class ConfiguracionAccionSeeder extends Seeder
{
    public function run(): void
    {
        $olimpiada = Olimpiada::where('estado', 1)->latest('id_olimpiada')->first()
                    ?? Olimpiada::latest('id_olimpiada')->first();

        if (!$olimpiada) {
            $this->command->warn('⚠️ No se encontró olimpiada activa. Ejecuta DemoCompetenciaSeeder primero.');
            return;
        }

        $this->command->info("🔹 Configurando permisos para: {$olimpiada->nombre}");

        $faseConfig = FaseGlobal::where('codigo', 'CONFIG')
                        ->where('id_olimpiada', $olimpiada->id_olimpiada)
                        ->first();

        $faseClasif = FaseGlobal::where('codigo', 'CLASIF')
                        ->where('id_olimpiada', $olimpiada->id_olimpiada)
                        ->first();

        $faseFinal  = FaseGlobal::where('codigo', 'FINAL')
                        ->where('id_olimpiada', $olimpiada->id_olimpiada)
                        ->first();

        $accionRegEstud   = AccionSistema::where('codigo', 'REG_ESTUD')->first();
        $accionCargarNotas= AccionSistema::where('codigo', 'CARGAR_NOTAS')->first();
        $accionPubClasif  = AccionSistema::where('codigo', 'PUB_CLASIF')->first();

        $matriz = [];

        if ($faseConfig) {
            $matriz[] = ['fase' => $faseConfig, 'accion' => $accionRegEstud, 'habilitada' => true];
            $matriz[] = ['fase' => $faseConfig, 'accion' => $accionCargarNotas, 'habilitada' => false];
        }

        if ($faseClasif) {
            $matriz[] = ['fase' => $faseClasif, 'accion' => $accionRegEstud, 'habilitada' => false];
            $matriz[] = ['fase' => $faseClasif, 'accion' => $accionCargarNotas, 'habilitada' => true];
        }

        if ($faseFinal) {
            $matriz[] = ['fase' => $faseFinal, 'accion' => $accionCargarNotas, 'habilitada' => true];
            $matriz[] = ['fase' => $faseFinal, 'accion' => $accionPubClasif, 'habilitada' => true];
        }

        foreach ($matriz as $item) {
            if ($item['fase'] && $item['accion']) {
                ConfiguracionAccion::updateOrCreate(
                    [
                        'id_fase_global'    => $item['fase']->id_fase_global,
                        'id_accion_sistema' => $item['accion']->id_accion_sistema,
                    ],
                    ['habilitada' => $item['habilitada']]
                );
            }
        }

        $this->command->info('Configuración de acciones inicializada.');
    }
}
