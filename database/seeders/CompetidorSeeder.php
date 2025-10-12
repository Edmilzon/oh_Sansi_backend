<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Persona;
use App\Models\Competidor;
use App\Models\Institucion;
use App\Models\Area;
use App\Models\AreaNivel;
use App\Models\Nivel;
use Illuminate\Support\Facades\DB;

class CompetidorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Competidor::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $instituciones = Institucion::all();

        $areaNiveles = AreaNivel::with('area')->get();

        if ($instituciones->isEmpty() || $areaNiveles->isEmpty()) {
            $this->command->warn('Por favor, ejecuta primero los seeders de Institucion, Area, Nivel y AreaNivel.');
            return;
        }

        $nombres = ['Carlos', 'Ana', 'Juan', 'Maria', 'Pedro', 'Laura', 'Luis', 'Sofia', 'Diego', 'Valentina', 'Javier', 'Camila'];
        $apellidos = ['Garcia', 'Rodriguez', 'Gonzalez', 'Fernandez', 'Lopez', 'Martinez', 'Sanchez', 'Perez', 'Gomez', 'Martin'];

        $personasAnteriores = Persona::where('email', 'like', '%@competidor.test')->get();
        foreach ($personasAnteriores as $persona) {
            if (!$persona->usuario) {
                $persona->delete();
            }
        }

        for ($i = 1; $i <= 15; $i++) {
            $nombre = $nombres[array_rand($nombres)];
            $apellido = $apellidos[array_rand($apellidos)];
            $email = strtolower($nombre . '.' . $apellido . $i . '@competidor.test');
            $genero = (in_array($nombre, ['Ana', 'Maria', 'Laura', 'Sofia', 'Valentina', 'Camila'])) ? 'F' : 'M';

            $persona = Persona::create([
                'nombre' => $nombre,
                'apellido' => $apellido,
                'ci' => rand(5000000, 9999999) . 'CB',
                'email' => $email,
                'telefono' => '7' . rand(1000000, 9999999),
                'fecha_nac' => "200" . rand(3, 8) . "-". str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) ."-". str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT),
                'genero' => $genero,
            ]);

            if ($i <= 7) {
                $areaNivelAsignado = $areaNiveles->whereIn('area.nombre', ['Matematicas', 'Fisica'])->random();
            } else {
                $areaNivelAsignado = $areaNiveles->random();
            }

            Competidor::create([
                'id_persona' => $persona->id_persona,
                'grado_escolar' => '5to de Secundaria',
                'departamento' => 'Cochabamba',
                'nombre_tutor' => "Tutor de {$nombre}",
                'contacto_tutor' => "7000000{$i}",
                'contacto_emergencia' => "7111111{$i}",
                'id_institucion' => $instituciones->random()->id_institucion,
                'id_area' => $areaNivelAsignado->id_area,
                'id_nivel' => $areaNivelAsignado->id_nivel,
            ]);
        }
    }
}