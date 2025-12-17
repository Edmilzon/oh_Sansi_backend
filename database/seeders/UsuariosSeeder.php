<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Model\Usuario;
use App\Model\Persona;
use App\Model\Rol;
use App\Model\UsuarioRol;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        $persona = Persona::firstOrCreate(
            ['ci' => 'ADMIN-001'],
            [
                'nombre'   => 'Super',
                'apellido' => 'Administrador',
                'telefono' => '00000000',
                'email'    => 'admin@ohsansi.com'
            ]
        );

        $usuario = Usuario::firstOrCreate(
            ['email' => 'admin@ohsansi.com'],
            [
                'id_persona' => $persona->id_persona,
                'password'   => Hash::make('admin123'),
            ]
        );

        $rolAdmin = Rol::where('nombre', 'Administrador')->first();

        if ($rolAdmin) {
            $existe = UsuarioRol::where('id_usuario', $usuario->id_usuario)
                                ->where('id_rol', $rolAdmin->id_rol)
                                ->exists();

            if (!$existe) {
                UsuarioRol::create([
                    'id_usuario'   => $usuario->id_usuario,
                    'id_rol'       => $rolAdmin->id_rol,
                    'id_olimpiada' => null
                ]);
            }
        } else {
            $this->command->error('El rol "Administrador" no existe. Ejecuta RolesSeeder primero.');
        }

        $this->command->info('Usuario Administrador creado: admin@ohsansi.com / admin123');
    }
}
