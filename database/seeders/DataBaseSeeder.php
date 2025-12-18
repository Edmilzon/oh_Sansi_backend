<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            FaseGlobalSeeder::class
        ]);
        
        $this->call([
            RolesSeeder::class,
            AccionSistemaSeeder::class,
            DepartamentoSeeder::class,
            AreasSeeder::class,
            NivelesSeeder::class,
            GradoEscolaridadSeeder::class,
            InstitucionSeeder::class,
        ]);

        $this->call([
            UsuariosSeeder::class,
        ]);
    }
}
