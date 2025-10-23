<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OlimpiadasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Inserta una olimpiada de ejemplo para que el admin pueda ser creado.
        DB::table('olimpiadas')->insert([
            'id_olimpiada' => 1,
            'nombre' => 'Olimpiada de Prueba',
            'gestion' => '2025',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
