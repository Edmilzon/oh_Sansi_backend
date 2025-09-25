<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Llama a todos los seeders que quieres ejecutar.
        // De esta forma, mantienes el código organizado.
        $this->call([
            CodigoEvaluadorSeeder::class,
            // Aquí puedes añadir otras clases Seeder que crees en el futuro.
        ]);
    }
}