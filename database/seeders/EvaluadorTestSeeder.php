<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Model\Usuario;
use App\Model\Olimpiada;
use App\Model\Area;
use App\Model\Nivel;
use App\Model\GradoEscolaridad;
use App\Model\Rol;
use App\Model\AreaNivel;
use App\Model\EvaluadorAn;

class EvaluadorTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('Iniciando EvaluadorTestSeeder...');

            // --- 1. Crear o encontrar el usuario evaluador ---
            $evaluadorUser = Usuario::firstOrCreate(
                ['ci' => '8888888'],
                [
                    'nombre' => 'Clao',
                    'apellido' => 'Test',
                    'email' => 'clao@gmail.com',
                    'password' => bcrypt('claotest'),
                    'telefono' => '78888888'
                ]
            );
            $this->command->info("Usuario evaluador '{$evaluadorUser->nombre} {$evaluadorUser->apellido}' creado/encontrado.");

            // --- 2. Obtener o crear entidades necesarias ---
            $olimpiada2025 = Olimpiada::firstOrCreate(['gestion' => '2025'], ['nombre' => 'Olimpiada Científica 2025']);
            $areaMatematicas = Area::firstOrCreate(['nombre' => 'Matemáticas']);
            $areaFisica = Area::firstOrCreate(['nombre' => 'Física']);
            $nivel1 = Nivel::firstOrCreate(['nombre' => 'Nivel 1']);
            $nivel2 = Nivel::firstOrCreate(['nombre' => 'Nivel 2']);
            $nivel3 = Nivel::firstOrCreate(['nombre' => 'Nivel 3']);
            $grado2do = GradoEscolaridad::firstOrCreate(['nombre' => '2do de Secundaria']);
            $grado3ro = GradoEscolaridad::firstOrCreate(['nombre' => '3ro de Secundaria']);
            $rolEvaluador = Rol::where('nombre', 'Evaluador')->first();

            if (!$rolEvaluador) {
                $this->command->error('El rol "Evaluador" no existe. Ejecuta RolesSeeder primero.');
                return;
            }

            // --- 3. Asignar rol de Evaluador para la gestión 2025 ---
            DB::table('usuario_rol')->insertOrIgnore([
                'id_usuario' => $evaluadorUser->id_usuario,
                'id_rol' => $rolEvaluador->id_rol,
                'id_olimpiada' => $olimpiada2025->id_olimpiada,
            ]);

            // --- 4. Crear las combinaciones de AreaNivel y asignarlas ---
            $asignaciones = [
                // Matemáticas
                ['id_area' => $areaMatematicas->id_area, 'id_nivel' => $nivel1->id_nivel, 'id_grado_escolaridad' => $grado2do->id_grado_escolaridad],
                ['id_area' => $areaMatematicas->id_area, 'id_nivel' => $nivel1->id_nivel, 'id_grado_escolaridad' => $grado3ro->id_grado_escolaridad],

                ['id_area' => $areaMatematicas->id_area, 'id_nivel' => $nivel2->id_nivel, 'id_grado_escolaridad' => $grado2do->id_grado_escolaridad],
                ['id_area' => $areaMatematicas->id_area, 'id_nivel' => $nivel2->id_nivel, 'id_grado_escolaridad' => $grado3ro->id_grado_escolaridad],

                ['id_area' => $areaMatematicas->id_area, 'id_nivel' => $nivel3->id_nivel, 'id_grado_escolaridad' => $grado2do->id_grado_escolaridad],
                ['id_area' => $areaMatematicas->id_area, 'id_nivel' => $nivel3->id_nivel, 'id_grado_escolaridad' => $grado3ro->id_grado_escolaridad],
                // Física
                ['id_area' => $areaFisica->id_area, 'id_nivel' => $nivel1->id_nivel, 'id_grado_escolaridad' => $grado2do->id_grado_escolaridad],
                ['id_area' => $areaFisica->id_area, 'id_nivel' => $nivel1->id_nivel, 'id_grado_escolaridad' => $grado3ro->id_grado_escolaridad],
                
                ['id_area' => $areaFisica->id_area, 'id_nivel' => $nivel2->id_nivel, 'id_grado_escolaridad' => $grado2do->id_grado_escolaridad],
                ['id_area' => $areaFisica->id_area, 'id_nivel' => $nivel2->id_nivel, 'id_grado_escolaridad' => $grado3ro->id_grado_escolaridad],

                
            ];

            foreach ($asignaciones as $asignacion) {
                // Crear la entrada en area_nivel
                $areaNivel = AreaNivel::firstOrCreate([
                    'id_area' => $asignacion['id_area'],
                    'id_nivel' => $asignacion['id_nivel'],
                    'id_grado_escolaridad' => $asignacion['id_grado_escolaridad'],
                    'id_olimpiada' => $olimpiada2025->id_olimpiada,
                ]);

                // Asignar al evaluador
                EvaluadorAn::firstOrCreate([
                    'id_usuario' => $evaluadorUser->id_usuario,
                    'id_area_nivel' => $areaNivel->id_area_nivel,
                ]);
            }

            $this->command->info('Asignaciones de áreas y niveles para el evaluador completadas.');
            $this->command->info('EvaluadorTestSeeder completado exitosamente!');
        });
    }
}
