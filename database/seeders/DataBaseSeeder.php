<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Model\Usuario;
use App\Model\Olimpiada;

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
            UsusariosSeeder::class,
            Olimpiada2023Seeder::class, // <-- Añadido el nuevo seeder
        ]);
    }
}