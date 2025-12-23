<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\RolAccion;
use App\Model\AccionSistema;
use App\Model\Rol;

class RolAccionSeeder extends Seeder
{
    public function run(): void
    {
        $rolAdmin = Rol::where('nombre', 'Administrador')->first();
        $accionesSistema = AccionSistema::all();

        if ($rolAdmin) {
            foreach ($accionesSistema as $accion) {
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
        }
    }
}
