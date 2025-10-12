<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Prerrequisitos
            AreaSeeder::class,
            NivelSeeder::class,
            AreaNivelSeeder::class,
            InstitucionSeeder::class,
            RegisterAdmin::class,
            ResponsableAreaSeeder::class,
            CompetidorSeeder::class,
        ]);
    }
}