<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Persona;
use App\Models\Usuario;

class RegisterAdmin extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Datos del administrador
        $adminPersona = Persona::firstOrCreate(
            ['email' => 'admin@ohsansi.com'],
            [
                'nombre' => 'Administrador',
                'apellido' => 'Principal',
                'ci' => '99999999',
                'fecha_nac' => '1990-01-01',
                'genero' => 'M',
                'telefono' => '70000000',
            ]
        );

        Usuario::firstOrCreate(
            ['id_persona' => $adminPersona->id_persona],
            [
                'nombre' => 'privilegiado',
                'password' => 'Admin1234*',
                'rol' => Usuario::ROL_ADMIN,
            ]
        );
    }
}