<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Persona;
use App\Models\Competidor;
use App\Models\Institucion;
use App\Models\Area;
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

        // Áreas específicas para el responsable y el resto para aleatoriedad
        $areasResponsable = Area::whereIn('nombre', ['Matematicas', 'Fisica'])->get();
        $areas = Area::all();

        $niveles = Nivel::all();

        if ($instituciones->isEmpty() || $areas->isEmpty() || $niveles->isEmpty()) {
            $this->command->warn('Por favor, ejecuta primero los seeders de Institucion, Area y Nivel.');
            return;
        }

        for ($i = 1; $i <= 10; $i++) {
            // Crear Persona
            $persona = Persona::create([
                'nombre' => "Competidor{$i}",
                'apellido' => "Apellido{$i}",
                'ci' => "111111{$i}",
                'email' => "competidor{$i}@test.com",
                'telefono' => "6000000{$i}",
                'fecha_nac' => "200{$i}-01-01",
                'genero' => ($i % 2 == 0) ? 'M' : 'F',
            ]);

            // Asignar área: los primeros 5 a las áreas del responsable, el resto aleatorio.
            if ($i <= 5 && !$areasResponsable->isEmpty()) {
                $areaAsignada = $areasResponsable->random();
            } else {
                $areaAsignada = $areas->random();
            }

            // Crear Competidor
            Competidor::create([
                'id_persona' => $persona->id_persona,
                'grado_escolar' => '5to de Secundaria',
                'departamento' => 'Cochabamba',
                'nombre_tutor' => "Tutor {$i}",
                'contacto_tutor' => "7000000{$i}",
                'contacto_emergencia' => "7111111{$i}",
                'id_institucion' => $instituciones->random()->id_institucion,
                'id_area' => $areaAsignada->id_area,
                'id_nivel' => $niveles->random()->id_nivel,
            ]);
        }
    }
}