<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Olimpiada;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            OlimpiadaSeeder::class, 
            AreasSeeder::class,
            NivelesSeeder::class,
            UsusariosSeeder::class,
            //Olimpiada2023Seeder::class,
            //Olimpiadas2024Seeder::class,
            DepartamentoSeeder::class,
           // TestUserSeeder::class,
           // EvaluadorTestSeeder::class,
            GradoEscolaridadSeeder::class,
            AreasEvaluadoresSeeder::class,
            CompetidorSeeder::class,
        ]);
    }
}