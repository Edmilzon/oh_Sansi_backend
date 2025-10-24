<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Olimpiada;

class DataBaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ejecutar otros seeders
        $this->call([
            RolesSeeder::class,
        ]);

        // Crear olimpiada de prueba
        $olimpiada = Olimpiada::create([
            'nombre' => 'Olimpiada Científica Estudiantil 2024',
            'gestion' => '2024',
        ]);

        // Crear usuarios de prueba para cada rol
        $usuarios = [
            [
                'nombre' => 'Admin',
                'apellido' => 'Sistema',
                'ci' => '12345678',
                'email' => 'admin@ohsansi.com',
                'password' => Hash::make('admin123'),
                'telefono' => '12345678',
            ],
            [
                'nombre' => 'Juan',
                'apellido' => 'Responsable',
                'ci' => '87654321',
                'email' => 'responsable@ohsansi.com',
                'password' => Hash::make('responsable123'),
                'telefono' => '87654321',
            ],
            [
                'nombre' => 'María',
                'apellido' => 'Evaluadora',
                'ci' => '11223344',
                'email' => 'evaluador@ohsansi.com',
                'password' => Hash::make('evaluador123'),
                'telefono' => '11223344',
            ],
        ];

        foreach ($usuarios as $index => $usuarioData) {
            $usuario = Usuario::create($usuarioData);
            
            // Asignar roles según el índice
            $roles = ['Administrador', 'Responsable Area', 'Evaluador'];
            $usuario->asignarRol($roles[$index], $olimpiada->id_olimpiada);
        }

        $this->command->info('Usuarios de prueba creados exitosamente:');
        $this->command->info('- Admin: CI 12345678, Password: admin123');
        $this->command->info('- Responsable: CI 87654321, Password: responsable123');
        $this->command->info('- Evaluador: CI 11223344, Password: evaluador123');
    }
}