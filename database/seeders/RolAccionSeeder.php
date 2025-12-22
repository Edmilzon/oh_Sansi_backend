<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Rol;
use App\Model\AccionSistema;
use App\Model\RolAccion;

class RolAccionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔗 Vinculando Catálogo Oficial al Administrador...');

        $rolAdmin = Rol::where('nombre', 'Administrador')->first();

        if (!$rolAdmin) {
            $this->command->warn('⚠️ Rol Administrador no encontrado. Saltando asignación.');
            return;
        }

        $todasLasAcciones = AccionSistema::all();

        foreach ($todasLasAcciones as $accion) {
            RolAccion::firstOrCreate(
                [
                    'id_rol' => $rolAdmin->id_rol,
                    'id_accion_sistema' => $accion->id_accion_sistema
                ],
                [
                    'activo' => true
                ]
            );
        }

        $this->command->info('✅ Super Admin sincronizado con las ' . $todasLasAcciones->count() . ' secciones oficiales.');
    }
}
