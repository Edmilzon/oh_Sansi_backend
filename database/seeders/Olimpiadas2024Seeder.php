<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Model\Olimpiada;
use App\Model\Area;
use App\Model\Nivel;
use App\Model\Usuario;
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

class Olimpiada2024Seeder extends Seeder
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

            // 6. Crear usuarios responsables y evaluadores para Química
            $responsableUser = Usuario::firstOrCreate([
                'ci' => '9988777',
            ], [
                'nombre' => 'Roberto',
                'apellido' => 'Gomez',
                'email' => 'roberto.gomez@test.com',
                'password' => bcrypt('password123')
            ]);
            $responsableUser->asignarRol('Responsable Area', $olimpiada->id_olimpiada);

            $evaluadorUser = Usuario::firstOrCreate([
                'ci' => '6655444',
            ], [
                'nombre' => 'Mariana',
                'apellido' => 'Salas',
                'email' => 'mariana.salas@test.com',
                'password' => bcrypt('password123')
            ]);
            $evaluadorUser->asignarRol('Evaluador', $olimpiada->id_olimpiada);

            $responsableQuimica = ResponsableArea::create([
                'id_usuario' => $responsableUser->id_usuario,
                'id_area_olimpiada' => $areaOlimpiadaQuimica->id_area_olimpiada
            ]);

            $evaluadorQuimica = EvaluadorAn::create([
                'id_usuario' => $evaluadorUser->id_usuario,
                'id_area_olimpiada' => $areaOlimpiadaQuimica->id_area_olimpiada
            ]);

            $this->command->info('Responsable y evaluador asignados a Química.');

            // 7. Crear Instituciones
            $institucion1 = Institucion::firstOrCreate(['nombre' => 'Colegio San Agustín']);
            $institucion2 = Institucion::firstOrCreate(['nombre' => 'Colegio Alemán']);

            // 8. Crear competidores para Química
            $competidoresQuimicaData = [
                ['nombre' => 'Ana','apellido' => 'Lopez','ci' => '5678901','grado' => '1ro de Secundaria','id_institucion' => $institucion1->id_institucion],
                ['nombre' => 'Carlos','apellido' => 'Perez','ci' => '6789012','grado' => '2do de Secundaria','id_institucion' => $institucion2->id_institucion],
            ];

            $competidoresQuimica = [];
            foreach ($competidoresQuimicaData as $data) {
                $competidoresQuimica[] = Competidor::create([
                    'datos' => json_encode([
                        'nombre' => $data['nombre'],
                        'apellido' => $data['apellido'],
                        'ci' => $data['ci'],
                        'grado' => $data['grado'],
                    ]),
                    'id_institucion' => $data['id_institucion'],
                    'id_area_nivel' => $areaNivelQuimica->id_area_nivel
                ]);
            }

            $this->command->info('Competidores de Química creados.');

            // 9. Crear evaluaciones
            foreach ($competidoresQuimica as $index => $comp) {
                Evaluacion::create([
                    'nota' => [90, 85][$index] ?? 0,
                    'fecha_evaluacion' => '2024-10-15',
                    'estado' => 'finalizado',
                    'id_evaluadorAN' => $evaluadorQuimica->id_evaluadorAN,
                    'id_competidor' => $comp->id_competidor,
                ]);
            }

            $this->command->info('Evaluaciones de Química creadas.');

            // 10. Crear competencia final
            $competencia = Competencia::create([
                'fecha_inicio' => '2024-11-01',
                'fecha_fin' => '2024-11-02',
                'estado' => 'En Curso',
                'id_fase' => $faseFinalQuimica->id_fase,
                'id_parametro' => $paramQuimica->id_parametro,
            ]);

            // 11. Crear grupo final y asignar competidores
            $grupoFinal = Grupo::create([
                'nombre' => 'Grupo Finalistas Química',
                'id_fase' => $faseFinalQuimica->id_fase
            ]);

            $grupoFinal->competidores()->attach(array_map(fn($c) => $c->id_competidor, $competidoresQuimica));

            $this->command->info('Grupo final de Química creado y competidores asignados.');

            // 12. Medallero
            foreach ($competidoresQuimica as $i => $comp) {
                Medallero::create([
                    'puesto' => $i+1,
                    'medalla' => ['Oro','Plata'][$i] ?? 'Sin medalla',
                    'id_competidor' => $comp->id_competidor,
                    'id_competencia' => $competencia->id_competencia
                ]);
            }

            $this->command->info('Medallero de Química generado.');

            // 13. Crear Aval
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
