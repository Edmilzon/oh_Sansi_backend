<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder; 
use App\Models\Usuario;

class RegisterAdmin extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear o encontrar el usuario administrador
        $admin = Usuario::firstOrCreate(
            [
                'email' => 'admin@ohsansi.com',
            ],
            [
                'nombre' => 'Administrador',
                'apellido' => 'Principal',
                'ci' => '99999999',
                'telefono' => '70000000',
                'password' => 'Admin1234',
            ]
        );

        $admin->asignarRol('Administrador', 1);
    }
}