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

            // 4. Crear Usuarios (Responsable y Evaluador para 2023)
            $responsableUser = Usuario::create([
                'nombre' => 'Carlos', 'apellido' => 'Perez', 'ci' => '9988776', 'email' => 'carlos.perez@test.com', 'password' => 'mundolibre'
            ]);
            $responsableUser->asignarRol('Responsable Area', $olimpiada->id_olimpiada);

            $evaluadorUser = Usuario::create([
                'nombre' => 'Lucia', 'apellido' => 'Mendez', 'ci' => '6655443', 'email' => 'lucia.mendez@test.com', 'password' => 'password12'
            ]);
            $evaluadorUser->asignarRol('Evaluador', $olimpiada->id_olimpiada);
            $this->command->info('Usuarios (Responsable y Evaluador) para 2023 creados.');

            // 5. Vincular usuarios a sus áreas
            $responsableArea = ResponsableArea::create([
                'id_usuario' => $responsableUser->id_usuario,
                'id_area_olimpiada' => $areaOlimpiadaIds['Matemáticas'],
            ]);
             $responsableAreaFis  = ResponsableArea::create([
                'id_usuario' => $responsableUser->id_usuario,
                'id_area_olimpiada' => $areaOlimpiadaIds['Física'],
            ]);
            $evaluadorAn = EvaluadorAn::create([
                'id_usuario' => $evaluadorUser->id_usuario,
                'id_area_olimpiada' => $areaOlimpiadaIds['Matemáticas'],
            ]);
            $this->command->info('Usuarios asignados como responsables de área.');

            // 6. Crear Instituciones
            $institucion1 = Institucion::create(['nombre' => 'Colegio Don Bosco']);
            $institucion2 = Institucion::create(['nombre' => 'Colegio La Salle']);

        //crear competidores para area y nivel
           // 1. Crear AreaNivel para Física (2 niveles) y Matemáticas (3 niveles)
$areas = Area::all();
$niveles = Nivel::all(); // Asegúrate de que los niveles ya estén creados

// --- Matemáticas ---
$areaMatematicas = $areas->firstWhere('nombre', 'Matemáticas');
$areaNivelesMatematicas = [];

foreach ($niveles->take(3) as $nivel) { // Solo los primeros 3 niveles
    $areaNivelesMatematicas[$nivel->id_nivel] = AreaNivel::create([
        'id_area' => $areaMatematicas->id_area,
        'id_nivel' => $nivel->id_nivel,
        'id_olimpiada' => $olimpiada->id_olimpiada,
        'activo' => true,
    ]);
}

// --- Física ---
$areaFisica = $areas->firstWhere('nombre', 'Física');
$areaNivelesFisica = [];
foreach ($niveles->take(2) as $nivel) { // Solo los primeros 2 niveles
    $areaNivelesFisica[$nivel->id_nivel] = AreaNivel::create([
        'id_area' => $areaFisica->id_area,
        'id_nivel' => $nivel->id_nivel,
        'id_olimpiada' => $olimpiada->id_olimpiada,
        'activo' => true,
    ]);
}

// 2. Crear Fases y Parámetros (solo ejemplo para Matemáticas)
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

// 3. Crear Competidores para Matemáticas (6 estudiantes)
$competidoresDataMat = [
    ['datos' => json_encode(['nombre' => 'Ana','apellido' => 'Vaca','ci' => '1234567','grado' => '1ro de Secundaria']), 'id_institucion' => $institucion1->id_institucion],
    ['datos' => json_encode(['nombre' => 'Juan','apellido' => 'Angel','ci' => '2345678','grado' => '1ro de Secundaria']), 'id_institucion' => $institucion1->id_institucion],
    ['datos' => json_encode(['nombre' => 'Sofia','apellido' => 'Rios','ci' => '3456789','grado' => '1ro de Secundaria']), 'id_institucion' => $institucion2->id_institucion],
    ['datos' => json_encode(['nombre' => 'Mateo','apellido' => 'Choque','ci' => '4567890','grado' => '1ro de Secundaria']), 'id_institucion' => $institucion2->id_institucion],
    ['datos' => json_encode(['nombre' => 'Lucas','apellido' => 'Vaca','ci' => '124557','grado' => '2do de Secundaria']), 'id_institucion' => $institucion1->id_institucion],
    ['datos' => json_encode(['nombre' => 'Fiorilo','apellido' => 'Angel','ci' => '2344566','grado' => '2do de Secundaria']), 'id_institucion' => $institucion1->id_institucion],
];

$competidores = [];
foreach ($competidoresDataMat as $data) {
    $nivelId = ($data['datos'] ? json_decode($data['datos'], true)['grado'] : '') === '1ro de Secundaria' 
        ? $niveles->first()->id_nivel // Primer nivel para 1ro
        : $niveles[1]->id_nivel;     // Segundo nivel para 2do
    $competidores[] = Competidor::create(array_merge($data, [
        'id_area_nivel' => $areaNivelesMatematicas[$nivelId]->id_area_nivel
    ]));
}

$this->command->info('Competidores de Matemáticas creados.');

// 4. Crear 1 competidor para Física (primer nivel)
$competidorFisica = Competidor::create([
    'datos' => json_encode(['nombre' => 'Pedro','apellido' => 'Lopez','ci' => '5678901','grado' => '1ro de Secundaria']),
    'id_institucion' => $institucion1->id_institucion,
    'id_area_nivel' => $areaNivelesFisica[$niveles->first()->id_nivel]->id_area_nivel
]);

$this->command->info('Competidor de Física creado.');
            // 9. Crear Evaluaciones
            $evaluaciones = [
                Evaluacion::create(['nota' => 95.50, 'fecha_evaluacion' => '2023-10-15', 'estado' => 'finalizado', 'id_evaluadorAN' => $evaluadorAn->id_evaluadorAN, 'id_competidor' => $competidores[0]->id_competidor]),
                Evaluacion::create(['nota' => 88.00, 'fecha_evaluacion' => '2023-10-15', 'estado' => 'finalizado', 'id_evaluadorAN' => $evaluadorAn->id_evaluadorAN, 'id_competidor' => $competidores[1]->id_competidor]),
                Evaluacion::create(['nota' => 76.50, 'fecha_evaluacion' => '2023-10-15', 'estado' => 'finalizado', 'id_evaluadorAN' => $evaluadorAn->id_evaluadorAN, 'id_competidor' => $competidores[2]->id_competidor]),
                Evaluacion::create(['nota' => 45.00, 'fecha_evaluacion' => '2023-10-15', 'estado' => 'finalizado', 'id_evaluadorAN' => $evaluadorAn->id_evaluadorAN, 'id_competidor' => $competidores[3]->id_competidor]),
            ];
            $this->command->info('Evaluaciones creadas.');

            // 10. Crear una Competencia (evento final)
            $competencia = Competencia::create([
                'fecha_inicio' => '2023-11-01',
                'fecha_fin' => '2023-11-02',
                'estado' => 'Finalizado',
                'id_fase' => $faseFinal->id_fase,
                'id_parametro' => $parametro->id_parametro,
            ]);
            $this->command->info('Registro de Competencia creado.');

            // 11. Crear Grupos y asignar competidores clasificados
            $grupoFinal = Grupo::create(['nombre' => 'Grupo Finalistas', 'id_fase' => $faseFinal->id_fase]);
            $grupoFinal->competidores()->attach([
                $competidores[0]->id_competidor,
                $competidores[1]->id_competidor,
                $competidores[2]->id_competidor
            ]); // Mateo no clasificó
            $this->command->info('Grupos y asignación de competidores finalistas creados.');

            // 12. Crear Medallero
            Medallero::create(['puesto' => 1, 'medalla' => 'Oro', 'id_competidor' => $competidores[0]->id_competidor, 'id_competencia' => $competencia->id_competencia]);
            Medallero::create(['puesto' => 2, 'medalla' => 'Plata', 'id_competidor' => $competidores[1]->id_competidor, 'id_competencia' => $competencia->id_competencia]);
            Medallero::create(['puesto' => 3, 'medalla' => 'Bronce', 'id_competidor' => $competidores[2]->id_competidor, 'id_competencia' => $competencia->id_competencia]);
            $this->command->info('Medallero generado.');

            // 13. Simular una desclasificación
            $competidorDescalificado = Competidor::create([
                'datos' => json_encode(['nombre' => 'Pedro', 'apellido' => 'Infante','ci' => '1232345','grado' => '1ro de Secundaria']),
                'id_institucion' => $institucion1->id_institucion,
                'id_area_nivel' => $areaNivelesMatematicas[$niveles->first()->id_nivel]->id_area_nivel

            ]);
            $evaluacionDescalificada = Evaluacion::create(['nota' => 0, 'fecha_evaluacion' => '2023-10-15', 'estado' => 'anulado', 'id_evaluadorAN' => $evaluadorAn->id_evaluadorAN, 'id_competidor' => $competidorDescalificado->id_competidor]);
            Desclasificaciones::create([
                'fecha' => '2023-10-16',
                'motivo' => 'Se detectó plagio durante la prueba.',
                'id_competidor' => $competidorDescalificado->id_competidor,
                'id_evaluacion' => $evaluacionDescalificada->id_evaluacion,
            ]);
            $this->command->info('Ejemplo de desclasificación creado.');

            // 14. Crear un Aval
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