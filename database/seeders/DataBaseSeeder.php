<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Model\Usuario;
use App\Model\Olimpiada;

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
            AreasSeeder::class,
            UsusariosSeeder::class,
        ]);
    }
}