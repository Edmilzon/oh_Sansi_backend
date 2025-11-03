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
use App\Model\Desclasificacion;
use App\Model\Aval;

class Olimpiada2023Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('Iniciando seeder para la Olimpiada 2023...');

            // 1. Crear la Olimpiada
            $olimpiada = Olimpiada::create([
                'nombre' => 'Olimpiada Científica Estudiantil 2023',
                'gestion' => '2023',
            ]);
            $this->command->info("Olimpiada '{$olimpiada->nombre}' creada.");

            // 2. Obtener Areas y Niveles (asumiendo que ya existen de otros seeders)
            $areas = Area::whereIn('nombre', ['Matemáticas', 'Física', 'Informática'])->get();
            if ($areas->isEmpty()) {
                $this->command->error('No se encontraron áreas base. Ejecuta AreasSeeder primero.');
                return;
            }
            $niveles = Nivel::all();
            if ($niveles->isEmpty()) {
                $this->command->error('No se encontraron niveles. Crea algunos niveles primero.');
                return;
            }

            // 3. Vincular Áreas con la Olimpiada 2023
            $areaOlimpiadaIds = [];
            foreach ($areas as $area) {
                $areaOlimpiada = AreaOlimpiada::create([
                    'id_area' => $area->id_area,
                    'id_olimpiada' => $olimpiada->id_olimpiada,
                ]);
                $areaOlimpiadaIds[$area->nombre] = $areaOlimpiada->id_area_olimpiada;
            }
            $this->command->info('Áreas vinculadas a la olimpiada.');

            // 4. Crear Personas primero
            $personasData = [
                ['nombre' => 'Carlos', 'apellido' => 'Perez', 'ci' => '9988776', 'email' => 'carlos.perez@test.com', 'genero' => 'M', 'telefono' => '77788899'],
                ['nombre' => 'Lucia', 'apellido' => 'Mendez', 'ci' => '6655443', 'email' => 'lucia.mendez@test.com', 'genero' => 'F', 'telefono' => '77788800'],
            ];

            $personas = [];
            foreach ($personasData as $data) {
                $personas[] = Persona::create($data);
            }

            // 5. Crear Usuarios (Responsable y Evaluador para 2023)
            $responsableUser = Usuario::create([
                'nombre' => 'Carlos',
                'apellido' => 'Perez', 
                'ci' => '9988776', 
                'email' => 'carlos.perez@test.com', 
                'password' => bcrypt('mundolibre'),
                'telefono' => '77788899'
            ]);

            $evaluadorUser = Usuario::create([
                'nombre' => 'Lucia',
                'apellido' => 'Mendez', 
                'ci' => '6655443', 
                'email' => 'lucia.mendez@test.com', 
                'password' => bcrypt('password12'),
                'telefono' => '77788800'
            ]);

            // Asignar roles (asumiendo que ya existen en la tabla rol)
            $rolResponsable = Rol::where('nombre', 'Responsable Area')->first();
            $rolEvaluador = Rol::where('nombre', 'Evaluador')->first();

            if ($rolResponsable && $rolEvaluador) {
                DB::table('usuario_rol')->insert([
                    ['id_usuario' => $responsableUser->id_usuario, 'id_rol' => $rolResponsable->id_rol, 'id_olimpiada' => $olimpiada->id_olimpiada],
                    ['id_usuario' => $evaluadorUser->id_usuario, 'id_rol' => $rolEvaluador->id_rol, 'id_olimpiada' => $olimpiada->id_olimpiada],
                ]);
            }
            $this->command->info('Usuarios (Responsable y Evaluador) para 2023 creados.');

            // 6. Vincular usuarios a sus áreas
            $responsableArea = ResponsableArea::create([
                'id_usuario' => $responsableUser->id_usuario,
                'id_area_olimpiada' => $areaOlimpiadaIds['Matemáticas'],
            ]);
            
            $responsableAreaFis = ResponsableArea::create([
                'id_usuario' => $responsableUser->id_usuario,
                'id_area_olimpiada' => $areaOlimpiadaIds['Física'],
            ]);
            
            $evaluadorAn = EvaluadorAn::create([
                'id_usuario' => $evaluadorUser->id_usuario,
                'id_area_olimpiada' => $areaOlimpiadaIds['Matemáticas'],
            ]);
            $this->command->info('Usuarios asignados como responsables de área.');

            // 7. Crear Instituciones
            $institucion1 = Institucion::create(['nombre' => 'Colegio Don Bosco']);
            $institucion2 = Institucion::create(['nombre' => 'Colegio La Salle']);

            // 8. Crear Personas para competidores
            $personasCompetidoresData = [
                ['nombre' => 'Ana', 'apellido' => 'Vaca', 'ci' => '1234567', 'email' => 'ana.vaca@test.com', 'genero' => 'F', 'telefono' => '77711111'],
                ['nombre' => 'Juan', 'apellido' => 'Angel', 'ci' => '2345678', 'email' => 'juan.angel@test.com', 'genero' => 'M', 'telefono' => '77711112'],
                ['nombre' => 'Sofia', 'apellido' => 'Rios', 'ci' => '3456789', 'email' => 'sofia.rios@test.com', 'genero' => 'F', 'telefono' => '77711113'],
                ['nombre' => 'Mateo', 'apellido' => 'Choque', 'ci' => '4567890', 'email' => 'mateo.choque@test.com', 'genero' => 'M', 'telefono' => '77711114'],
                ['nombre' => 'Lucas', 'apellido' => 'Vaca', 'ci' => '124557', 'email' => 'lucas.vaca@test.com', 'genero' => 'M', 'telefono' => '77711115'],
                ['nombre' => 'Fiorilo', 'apellido' => 'Angel', 'ci' => '2344566', 'email' => 'fiorilo.angel@test.com', 'genero' => 'M', 'telefono' => '77711116'],
                ['nombre' => 'Pedro', 'apellido' => 'Lopez', 'ci' => '5678901', 'email' => 'pedro.lopez@test.com', 'genero' => 'M', 'telefono' => '77711117'],
                ['nombre' => 'Pedro', 'apellido' => 'Infante', 'ci' => '1232345', 'email' => 'pedro.infante@test.com', 'genero' => 'M', 'telefono' => '77711118'],
            ];

            $personasCompetidores = [];
            foreach ($personasCompetidoresData as $data) {
                $personasCompetidores[] = Persona::create($data);
            }

            // 9. Crear AreaNivel para Física (2 niveles) y Matemáticas (3 niveles)
            $areaMatematicas = $areas->firstWhere('nombre', 'Matemáticas');
            $areaNivelesMatematicas = [];

            foreach ($niveles->take(3) as $nivel) {
                $areaNivelesMatematicas[$nivel->id_nivel] = AreaNivel::create([
                    'id_area' => $areaMatematicas->id_area,
                    'id_nivel' => $nivel->id_nivel,
                    'id_olimpiada' => $olimpiada->id_olimpiada,
                    'activo' => true,
                ]);
            }

            $areaFisica = $areas->firstWhere('nombre', 'Física');
            $areaNivelesFisica = [];
            foreach ($niveles->take(2) as $nivel) {
                $areaNivelesFisica[$nivel->id_nivel] = AreaNivel::create([
                    'id_area' => $areaFisica->id_area,
                    'id_nivel' => $nivel->id_nivel,
                    'id_olimpiada' => $olimpiada->id_olimpiada,
                    'activo' => true,
                ]);
            }

            // 10. Crear Fases y Parámetros (solo ejemplo para Matemáticas - primer nivel)
            $faseClasificatoria = Fase::create([
                'nombre' => 'Clasificatoria',
                'orden' => 1,
                'id_area_nivel' => $areaNivelesMatematicas[$niveles->first()->id_nivel]->id_area_nivel
            ]);
            
            $faseFinal = Fase::create([
                'nombre' => 'Final',
                'orden' => 2,
                'id_area_nivel' => $areaNivelesMatematicas[$niveles->first()->id_nivel]->id_area_nivel
            ]);

            $parametro = Parametro::create([
                'nota_max_clasif' => 100,
                'nota_min_clasif' => 51,
                'cantidad_max_apro' => 10,
                'id_area_nivel' => $areaNivelesMatematicas[$niveles->first()->id_nivel]->id_area_nivel
            ]);

            $this->command->info('Fases y parámetros creados.');

            // 11. Crear Competidores para Matemáticas (6 estudiantes)
            $competidoresDataMat = [
                ['grado_escolar' => '1ro de Secundaria', 'departamento' => 'La Paz', 'contacto_tutor' => '77722222', 'id_institucion' => $institucion1->id_institucion, 'id_persona' => $personasCompetidores[0]->id_persona],
                ['grado_escolar' => '1ro de Secundaria', 'departamento' => 'La Paz', 'contacto_tutor' => '77722223', 'id_institucion' => $institucion1->id_institucion, 'id_persona' => $personasCompetidores[1]->id_persona],
                ['grado_escolar' => '1ro de Secundaria', 'departamento' => 'Cochabamba', 'contacto_tutor' => '77722224', 'id_institucion' => $institucion2->id_institucion, 'id_persona' => $personasCompetidores[2]->id_persona],
                ['grado_escolar' => '1ro de Secundaria', 'departamento' => 'Cochabamba', 'contacto_tutor' => '77722225', 'id_institucion' => $institucion2->id_institucion, 'id_persona' => $personasCompetidores[3]->id_persona],
                ['grado_escolar' => '2do de Secundaria', 'departamento' => 'La Paz', 'contacto_tutor' => '77722226', 'id_institucion' => $institucion1->id_institucion, 'id_persona' => $personasCompetidores[4]->id_persona],
                ['grado_escolar' => '2do de Secundaria', 'departamento' => 'La Paz', 'contacto_tutor' => '77722227', 'id_institucion' => $institucion1->id_institucion, 'id_persona' => $personasCompetidores[5]->id_persona],
            ];

            $competidores = [];
            foreach ($competidoresDataMat as $data) {
                $nivelId = $data['grado_escolar'] === '1ro de Secundaria' 
                    ? $niveles->first()->id_nivel
                    : $niveles[1]->id_nivel;
                
                $competidores[] = Competidor::create(array_merge($data, [
                    'id_area_nivel' => $areaNivelesMatematicas[$nivelId]->id_area_nivel
                ]));
            }

            $this->command->info('Competidores de Matemáticas creados.');

            // 12. Crear 1 competidor para Física (primer nivel)
            $competidorFisica = Competidor::create([
                'grado_escolar' => '1ro de Secundaria',
                'departamento' => 'La Paz',
                'contacto_tutor' => '77722228',
                'id_institucion' => $institucion1->id_institucion,
                'id_area_nivel' => $areaNivelesFisica[$niveles->first()->id_nivel]->id_area_nivel,
                'id_persona' => $personasCompetidores[6]->id_persona
            ]);

            $this->command->info('Competidor de Física creado.');

            // 13. Crear Evaluaciones
            $evaluaciones = [
                Evaluacion::create([
                    'nota' => 95.50, 
                    'fecha_evaluacion' => '2023-10-15', 
                    'estado' => 'finalizado', 
                    'id_evaluadorAN' => $evaluadorAn->id_evaluadorAN, 
                    'id_competidor' => $competidores[0]->id_competidor
                ]),
                Evaluacion::create([
                    'nota' => 88.00, 
                    'fecha_evaluacion' => '2023-10-15', 
                    'estado' => 'finalizado', 
                    'id_evaluadorAN' => $evaluadorAn->id_evaluadorAN, 
                    'id_competidor' => $competidores[1]->id_competidor
                ]),
                Evaluacion::create([
                    'nota' => 76.50, 
                    'fecha_evaluacion' => '2023-10-15', 
                    'estado' => 'finalizado', 
                    'id_evaluadorAN' => $evaluadorAn->id_evaluadorAN, 
                    'id_competidor' => $competidores[2]->id_competidor
                ]),
                Evaluacion::create([
                    'nota' => 45.00, 
                    'fecha_evaluacion' => '2023-10-15', 
                    'estado' => 'finalizado', 
                    'id_evaluadorAN' => $evaluadorAn->id_evaluadorAN, 
                    'id_competidor' => $competidores[3]->id_competidor
                ]),
            ];
            $this->command->info('Evaluaciones creadas.');

            // 14. Crear una Competencia (evento final)
            $competencia = Competencia::create([
                'fecha_inicio' => '2023-11-01',
                'fecha_fin' => '2023-11-02',
                'estado' => 'Finalizado',
                'id_fase' => $faseFinal->id_fase,
                'id_parametro' => $parametro->id_parametro,
                'id_evaluacion' => $evaluaciones[0]->id_evaluacion,
            ]);
            $this->command->info('Registro de Competencia creado.');

            // 15. Crear Grupos y asignar competidores clasificados
            $grupoFinal = Grupo::create([
                'nombre' => 'Grupo Finalistas', 
            ]);
            
            // Asignar competidores al grupo usando la tabla pivote
            $grupoFinal->competidores()->attach([
                $competidores[0]->id_competidor,
                $competidores[1]->id_competidor,
                $competidores[2]->id_competidor
            ]);
            $this->command->info('Grupos y asignación de competidores finalistas creados.');

            // 16. Crear Medallero
            Medallero::create([
                'puesto' => 1, 
                'medalla' => 'Oro', 
                'id_competidor' => $competidores[0]->id_competidor, 
                'id_competencia' => $competencia->id_competencia
            ]);
            Medallero::create([
                'puesto' => 2, 
                'medalla' => 'Plata', 
                'id_competidor' => $competidores[1]->id_competidor, 
                'id_competencia' => $competencia->id_competencia
            ]);
            Medallero::create([
                'puesto' => 3, 
                'medalla' => 'Bronce', 
                'id_competidor' => $competidores[2]->id_competidor, 
                'id_competencia' => $competencia->id_competencia
            ]);
            $this->command->info('Medallero generado.');

            // 17. Simular una desclasificación
            $competidorDescalificado = Competidor::create([
                'grado_escolar' => '1ro de Secundaria',
                'departamento' => 'La Paz',
                'contacto_tutor' => '77722229',
                'id_institucion' => $institucion1->id_institucion,
                'id_area_nivel' => $areaNivelesMatematicas[$niveles->first()->id_nivel]->id_area_nivel,
                'id_persona' => $personasCompetidores[7]->id_persona
            ]);

            $evaluacionDescalificada = Evaluacion::create([
                'nota' => 0, 
                'fecha_evaluacion' => '2023-10-15', 
                'estado' => 'anulado', 
                'id_evaluadorAN' => $evaluadorAn->id_evaluadorAN, 
                'id_competidor' => $competidorDescalificado->id_competidor
            ]);
            
            Desclasificacion::create([
                'fecha' => '2023-10-16',
                'motivo' => 'Se detectó plagio durante la prueba.',
                'id_competidor' => $competidorDescalificado->id_competidor,
                'id_evaluacion' => $evaluacionDescalificada->id_evaluacion,
            ]);
            $this->command->info('Ejemplo de desclasificación creado.');

            // 18. Crear un Aval
            Aval::create([
                'fecha_aval' => '2023-11-05',
                'estado' => 'Aprobado',
                'id_competencia' => $competencia->id_competencia,
                'id_fase' => $faseFinal->id_fase,
                'id_responsableArea' => $responsableArea->id_responsableArea,
            ]);
            $this->command->info('Aval de resultados creado.');

            $this->command->info('¡Seeder de Olimpiada 2023 completado exitosamente!');
        });
    }
}