<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Model\Olimpiada;
use App\Model\Area;
use App\Model\Nivel;
use App\Model\Persona;
use App\Model\Usuario;
use App\Model\Rol;
use App\Model\Institucion;
use App\Model\AreaOlimpiada;
use App\Model\AreaNivel;
use App\Model\Fase;
use App\Model\Parametro;
use App\Model\ResponsableArea;
use App\Model\EvaluadorAn;
use App\Model\Competidor;
use App\Model\Evaluacion;
use App\Model\Grupo;
use App\Model\Competencia;
use App\Model\Medallero;
use App\Model\Aval;

class Olimpiadas2024Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('Iniciando seeder para la Olimpiada 2024...');

            // 1. Crear la Olimpiada 2024
            $olimpiada = Olimpiada::create([
                'nombre' => 'Olimpiada Científica Estudiantil 2024',
                'gestion' => '2024',
            ]);

            $this->command->info("Olimpiada '{$olimpiada->nombre}' creada.");

            // 2. Obtener o crear Área Química y Niveles
            $areaQuimica = Area::firstOrCreate(['nombre' => 'Química']);
            $nivelQuimica = Nivel::first(); // ejemplo: primer nivel disponible

            // 3. Vincular Química con la Olimpiada 2024
            $areaOlimpiadaQuimica = AreaOlimpiada::create([
                'id_area' => $areaQuimica->id_area,
                'id_olimpiada' => $olimpiada->id_olimpiada,
            ]);

            // 4. Crear AreaNivel para Química
            $areaNivelQuimica = AreaNivel::create([
                'id_area' => $areaQuimica->id_area,
                'id_nivel' => $nivelQuimica->id_nivel,
                'id_olimpiada' => $olimpiada->id_olimpiada,
                'activo' => true,
            ]);

            // 5. Crear fases y parámetros para Química
            $faseClasQuimica = Fase::create([
                'nombre' => 'Clasificatoria',
                'orden' => 1,
                'id_area_nivel' => $areaNivelQuimica->id_area_nivel
            ]);

            $faseFinalQuimica = Fase::create([
                'nombre' => 'Final',
                'orden' => 2,
                'id_area_nivel' => $areaNivelQuimica->id_area_nivel
            ]);

            $paramQuimica = Parametro::create([
                'nota_max_clasif' => 100,
                'nota_min_clasif' => 60,
                'cantidad_max_apro' => 15,
                'id_area_nivel' => $areaNivelQuimica->id_area_nivel
            ]);

            $this->command->info('Fases y parámetros creados para Química.');

            // 6. Crear Personas primero
            $personasData = [
                ['nombre' => 'Roberto', 'apellido' => 'Gomez', 'ci' => '9988777', 'email' => 'roberto.gomez@test.com', 'genero' => 'M', 'telefono' => '77788877'],
                ['nombre' => 'Mariana', 'apellido' => 'Salas', 'ci' => '6655444', 'email' => 'mariana.salas@test.com', 'genero' => 'F', 'telefono' => '77788844'],
                ['nombre' => 'Pedro', 'apellido' => 'Lopez', 'ci' => '5678901', 'email' => 'pedro.lopez@test.com', 'genero' => 'M', 'telefono' => '77711117'],
                ['nombre' => 'Juan', 'apellido' => 'Tiburcio', 'ci' => '6789020', 'email' => 'juan.tiburcio@test.com', 'genero' => 'M', 'telefono' => '77711122'],
            ];

            $personas = [];
                foreach ($personasData as $data) {
                $personas[] = Persona::firstOrCreate(
                ['ci' => $data['ci']],
            $data
            );
        }

            // 7. Crear usuarios responsables y evaluadores para Química
            $responsableUser = Usuario::firstOrCreate([
                'ci' => '9988777',
            ], [
                'nombre' => 'Roberto',
                'apellido' => 'Gomez',
                'email' => 'roberto.gomez@test.com',
                'password' => bcrypt('password123'),
                'telefono' => '77788877'
            ]);

            $evaluadorUser = Usuario::firstOrCreate([
                'ci' => '6655444',
            ], [
                'nombre' => 'Mariana',
                'apellido' => 'Salas',
                'email' => 'mariana.salas@test.com',
                'password' => bcrypt('password123'),
                'telefono' => '77788844'
            ]);

            // Asignar roles
            $rolResponsable = Rol::where('nombre', 'Responsable Area')->first();
            $rolEvaluador = Rol::where('nombre', 'Evaluador')->first();

            if ($rolResponsable && $rolEvaluador) {
                DB::table('usuario_rol')->insert([
                    ['id_usuario' => $responsableUser->id_usuario, 'id_rol' => $rolResponsable->id_rol, 'id_olimpiada' => $olimpiada->id_olimpiada],
                    ['id_usuario' => $evaluadorUser->id_usuario, 'id_rol' => $rolEvaluador->id_rol, 'id_olimpiada' => $olimpiada->id_olimpiada],
                ]);
            }

            $responsableQuimica = ResponsableArea::create([
                'id_usuario' => $responsableUser->id_usuario,
                'id_area_olimpiada' => $areaOlimpiadaQuimica->id_area_olimpiada
            ]);

            $evaluadorQuimica = EvaluadorAn::create([
                'id_usuario' => $evaluadorUser->id_usuario,
                'id_area_olimpiada' => $areaOlimpiadaQuimica->id_area_olimpiada
            ]);

            $this->command->info('Responsable y evaluador asignados a Química.');

            // 8. Crear Instituciones
            $institucion1 = Institucion::firstOrCreate(['nombre' => 'Colegio San Agustín']);
            $institucion2 = Institucion::firstOrCreate(['nombre' => 'Colegio Alemán']);

            // 9. Crear competidores para Química
            $competidoresQuimicaData = [
                [
                    'grado_escolar' => '1ro de Secundaria', 
                    'departamento' => 'La Paz', 
                    'contacto_tutor' => '77722230', 
                    'id_institucion' => $institucion1->id_institucion, 
                    'id_persona' => $personas[2]->id_persona
                ],
                [
                    'grado_escolar' => '2do de Secundaria', 
                    'departamento' => 'Cochabamba', 
                    'contacto_tutor' => '77722231', 
                    'id_institucion' => $institucion2->id_institucion, 
                    'id_persona' => $personas[3]->id_persona
                ],
            ];

            $competidoresQuimica = [];
            foreach ($competidoresQuimicaData as $data) {
                $competidoresQuimica[] = Competidor::create(array_merge($data, [
                    'id_area_nivel' => $areaNivelQuimica->id_area_nivel
                ]));
            }

            $this->command->info('Competidores de Química creados.');

            // 10. Crear evaluaciones
            $evaluaciones = [];
            foreach ($competidoresQuimica as $index => $comp) {
                $evaluaciones[] = Evaluacion::create([
                    'nota' => [90, 85][$index] ?? 0,
                    'fecha_evaluacion' => '2024-10-15',
                    'estado' => 'finalizado',
                    'id_evaluadorAN' => $evaluadorQuimica->id_evaluadorAN,
                    'id_competidor' => $comp->id_competidor,
                ]);
            }

            $this->command->info('Evaluaciones de Química creadas.');

            // 11. Crear competencia final
            $competencia = Competencia::create([
                'fecha_inicio' => '2024-11-01',
                'fecha_fin' => '2024-11-02',
                'estado' => 'En Curso',
                'id_fase' => $faseFinalQuimica->id_fase,
                'id_parametro' => $paramQuimica->id_parametro,
                'id_evaluacion' => $evaluaciones[0]->id_evaluacion,
            ]);

            // 12. Crear grupo final y asignar competidores
            $grupoFinal = Grupo::create([
                'nombre' => 'Grupo Finalistas Química',
            ]);

            // Asignar competidores al grupo usando la tabla pivote
            $grupoFinal->competidores()->attach(array_map(fn($c) => $c->id_competidor, $competidoresQuimica));

            $this->command->info('Grupo final de Química creado y competidores asignados.');

            // 13. Medallero
            foreach ($competidoresQuimica as $i => $comp) {
                Medallero::create([
                    'puesto' => $i+1,
                    'medalla' => ['Oro','Plata'][$i] ?? 'Sin medalla',
                    'id_competidor' => $comp->id_competidor,
                    'id_competencia' => $competencia->id_competencia
                ]);
            }

            $this->command->info('Medallero de Química generado.');

            // 14. Crear Aval
            Aval::create([
                'fecha_aval' => '2024-11-05',
                'estado' => 'Pendiente',
                'id_competencia' => $competencia->id_competencia,
                'id_fase' => $faseFinalQuimica->id_fase,
                'id_responsableArea' => $responsableQuimica->id_responsableArea
            ]);

            $this->command->info('Aval de resultados de Química creado.');
            $this->command->info('¡Seeder Olimpiada 2024 completado exitosamente!');
        });
    }
}