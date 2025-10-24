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
use App\Model\Desclasificaciones;
use App\Model\Aval;

class Olimpiadas2024Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('Iniciando seeder para la Olimpiada 2024...');

            // 1. Crear la Olimpiada
            $olimpiada = Olimpiada::create([
                'nombre' => 'Olimpiada Científica Estudiantil 2024',
                'gestion' => '2024',
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

            // 3. Vincular Áreas con la Olimpiada 2024
            $areaOlimpiadaIds = [];
            foreach ($areas as $area) {
                $areaOlimpiada = AreaOlimpiada::create([
                    'id_area' => $area->id_area,
                    'id_olimpiada' => $olimpiada->id_olimpiada,
                ]);
                $areaOlimpiadaIds[$area->nombre] = $areaOlimpiada->id_area_olimpiada;
            }
            $this->command->info('Áreas vinculadas a la olimpiada 2024.');

            // 4. Crear Usuarios (Responsable y Evaluador para 2024)
            $responsableUser = Usuario::create([
                'nombre' => 'Roberto', 'apellido' => 'Gomez', 'ci' => '9988777', 'email' => 'roberto.gomez@test.com', 'password' => 'password123'
            ]);
            $responsableUser->asignarRol('Responsable Area', $olimpiada->id_olimpiada);

            $evaluadorUser = Usuario::create([
                'nombre' => 'Mariana', 'apellido' => 'Salas', 'ci' => '6655444', 'email' => 'mariana.salas@test.com', 'password' => 'password123'
            ]);
            $evaluadorUser->asignarRol('Evaluador', $olimpiada->id_olimpiada);
            $this->command->info('Usuarios (Responsable y Evaluador) para 2024 creados.');

            // 5. Vincular usuarios a sus áreas
            $responsableArea = ResponsableArea::create([
                'id_usuario' => $responsableUser->id_usuario,
                'id_area_olimpiada' => $areaOlimpiadaIds['Física'],
            ]);
            $evaluadorAn = EvaluadorAn::create([
                'id_usuario' => $evaluadorUser->id_usuario,
                'id_area_olimpiada' => $areaOlimpiadaIds['Física'],
            ]);
            $this->command->info('Usuarios asignados como responsables de área para 2024.');

            // 6. Usar Instituciones existentes o crear nuevas si es necesario
            $institucion1 = Institucion::firstOrCreate(['nombre' => 'Colegio San Agustín']);
            $institucion2 = Institucion::firstOrCreate(['nombre' => 'Colegio Alemán']);

            // 7. Crear Area-Nivel, Fases y Parámetros para Física
            $areaNivelFisica = AreaNivel::create([
                'id_area' => $areas->firstWhere('nombre', 'Física')->id_area,
                'id_nivel' => $niveles->skip(1)->first()->id_nivel, // 2do de Secundaria
                'id_olimpiada' => $olimpiada->id_olimpiada,
                'activo' => true, // Competencia activa
            ]);

            $faseClasificatoria = Fase::create(['nombre' => 'Clasificatoria', 'orden' => 1, 'id_area_nivel' => $areaNivelFisica->id_area_nivel]);
            $faseFinal = Fase::create(['nombre' => 'Final', 'orden' => 2, 'id_area_nivel' => $areaNivelFisica->id_area_nivel]);

            $parametro = Parametro::create([
                'nota_max_clasif' => 100, 'nota_min_clasif' => 60, 'cantidad_max_apro' => 15, 'id_area_nivel' => $areaNivelFisica->id_area_nivel
            ]);
            $this->command->info('Configuración de Fases y Parámetros para Física creada.');

            // 8. Crear Competidores
            $competidoresData = [
                ['datos' => json_encode(['nombre' => 'Laura', 'apellido' => 'Vaca']), 'id_institucion' => $institucion1->id_institucion],
                ['datos' => json_encode(['nombre' => 'Miguel', 'apellido' => 'Angel']), 'id_institucion' => $institucion1->id_institucion],
                ['datos' => json_encode(['nombre' => 'Valeria', 'apellido' => 'Rios']), 'id_institucion' => $institucion2->id_institucion],
                ['datos' => json_encode(['nombre' => 'Andres', 'apellido' => 'Choque']), 'id_institucion' => $institucion2->id_institucion],
            ];

            $competidores = [];
            foreach ($competidoresData as $data) {
                $competidores[] = Competidor::create(array_merge($data, [
                    'id_area_nivel' => $areaNivelFisica->id_area_nivel
                ]));
            }
            $this->command->info('Competidores para 2024 creados.');

            // 9. Crear Evaluaciones
            $evaluaciones = [
                Evaluacion::create(['nota' => 98.00, 'fecha_evaluacion' => '2024-10-15', 'estado' => 'finalizado', 'id_evaluadorAN' => $evaluadorAn->id_evaluadorAN, 'id_competidor' => $competidores[0]->id_competidor]),
                Evaluacion::create(['nota' => 91.50, 'fecha_evaluacion' => '2024-10-15', 'estado' => 'finalizado', 'id_evaluadorAN' => $evaluadorAn->id_evaluadorAN, 'id_competidor' => $competidores[1]->id_competidor]),
                Evaluacion::create(['nota' => 85.00, 'fecha_evaluacion' => '2024-10-15', 'estado' => 'finalizado', 'id_evaluadorAN' => $evaluadorAn->id_evaluadorAN, 'id_competidor' => $competidores[2]->id_competidor]),
                Evaluacion::create(['nota' => 55.00, 'fecha_evaluacion' => '2024-10-15', 'estado' => 'finalizado', 'id_evaluadorAN' => $evaluadorAn->id_evaluadorAN, 'id_competidor' => $competidores[3]->id_competidor]),
            ];
            $this->command->info('Evaluaciones para 2024 creadas.');

            // 10. Crear una Competencia (evento final)
            $competencia = Competencia::create([
                'fecha_inicio' => '2024-11-01',
                'fecha_fin' => '2024-11-02',
                'estado' => 'En Curso',
                'id_fase' => $faseFinal->id_fase,
                'id_parametro' => $parametro->id_parametro,
            ]);
            $this->command->info('Registro de Competencia 2024 creado.');

            // 11. Crear Grupos y asignar competidores clasificados
            $grupoFinal = Grupo::create(['nombre' => 'Grupo Finalistas Física', 'id_fase' => $faseFinal->id_fase]);
            $grupoFinal->competidores()->attach([
                $competidores[0]->id_competidor,
                $competidores[1]->id_competidor,
                $competidores[2]->id_competidor
            ]); // Andres no clasificó
            $this->command->info('Grupos y asignación de competidores finalistas para 2024 creados.');

            // 12. Crear Medallero
            Medallero::create(['puesto' => 1, 'medalla' => 'Oro', 'id_competidor' => $competidores[0]->id_competidor, 'id_competencia' => $competencia->id_competencia]);
            Medallero::create(['puesto' => 2, 'medalla' => 'Plata', 'id_competidor' => $competidores[1]->id_competidor, 'id_competencia' => $competencia->id_competencia]);
            Medallero::create(['puesto' => 3, 'medalla' => 'Bronce', 'id_competidor' => $competidores[2]->id_competidor, 'id_competencia' => $competencia->id_competencia]);
            $this->command->info('Medallero 2024 generado.');

            // 13. Simular una desclasificación
            $competidorDescalificado = Competidor::create([
                'datos' => json_encode(['nombre' => 'Luis', 'apellido' => 'Peralta']),
                'id_institucion' => $institucion1->id_institucion,
                'id_area_nivel' => $areaNivelFisica->id_area_nivel
            ]);
            $evaluacionDescalificada = Evaluacion::create(['nota' => 0, 'fecha_evaluacion' => '2024-10-15', 'estado' => 'anulado', 'id_evaluadorAN' => $evaluadorAn->id_evaluadorAN, 'id_competidor' => $competidorDescalificado->id_competidor]);
            Desclasificaciones::create([
                'fecha' => '2024-10-16',
                'motivo' => 'Uso de dispositivos no autorizados.',
                'id_competidor' => $competidorDescalificado->id_competidor,
                'id_evaluacion' => $evaluacionDescalificada->id_evaluacion,
            ]);
            $this->command->info('Ejemplo de desclasificación para 2024 creado.');

            // 14. Crear un Aval
            Aval::create([
                'fecha_aval' => '2024-11-05',
                'estado' => 'Pendiente',
                'id_competencia' => $competencia->id_competencia,
                'id_fase' => $faseFinal->id_fase,
                'id_responsableArea' => $responsableArea->id_responsableArea,
            ]);
            $this->command->info('Aval de resultados para 2024 creado.');

            $this->command->info('¡Seeder de Olimpiada 2024 completado exitosamente!');
        });
    }
}