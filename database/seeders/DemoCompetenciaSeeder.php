<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DemoCompetenciaSeeder extends Seeder
{
    public function run(): void
    {
        $gestion = date('Y');

        // 1. Crear Olimpiada
        $idOlimpiada = DB::table('olimpiada')->insertGetId([
            'nombre' => "Olimpiada Científica $gestion",
            'gestion' => $gestion,
            'estado' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // 2. Fases Globales
        DB::table('fase_global')->insert([
            [
                'id_olimpiada' => $idOlimpiada,
                'nombre' => '1ra Etapa Distrital',
                'codigo' => 'CLASIF',
                'orden' => 1,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id_olimpiada' => $idOlimpiada,
                'nombre' => 'Etapa Final Departamental',
                'codigo' => 'FINAL',
                'orden' => 2,
                'created_at' => now(), 'updated_at' => now(),
            ]
        ]);

        $idFaseFinal = DB::table('fase_global')
            ->where('id_olimpiada', $idOlimpiada)
            ->where('codigo', 'FINAL')
            ->value('id_fase_global');

        // 3. Cronograma
        DB::table('cronograma_fase')->insert([
            'id_fase_global' => $idFaseFinal,
            'fecha_inicio' => Carbon::now()->subDays(5),
            'fecha_fin' => Carbon::now()->addDays(20),
            'estado' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // 4. Áreas y Niveles
        $idArea = 1;
        $idNivel = 1;

        $idAreaOli = DB::table('area_olimpiada')->insertGetId([
            'id_area' => $idArea,
            'id_olimpiada' => $idOlimpiada,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $idAreaNivel = DB::table('area_nivel')->insertGetId([
            'id_area_olimpiada' => $idAreaOli,
            'id_nivel' => $idNivel,
            'es_activo' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // 5. Usuarios (Responsable y Jueces)
        $idP_Resp = DB::table('persona')->insertGetId([
            'nombre' => 'Roberto', 'apellido' => 'Responsable',
            'ci' => 'RESP-2025', 'telefono' => '70000001',
            'email' => 'resp@demo.com',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $idU_Resp = DB::table('usuario')->insertGetId([
            'id_persona' => $idP_Resp,
            'email' => 'resp@demo.com',
            'password' => Hash::make('password'),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        DB::table('usuario_rol')->insert([
            'id_usuario' => $idU_Resp, 'id_rol' => 2, 'id_olimpiada' => $idOlimpiada, 'created_at' => now(), 'updated_at' => now()
        ]);
        DB::table('responsable_area')->insert([
            'id_usuario' => $idU_Resp, 'id_area_olimpiada' => $idAreaOli, 'created_at' => now(), 'updated_at' => now()
        ]);

        // Juez 1
        $idP_Eval1 = DB::table('persona')->insertGetId([
            'nombre' => 'Elena', 'apellido' => 'Jueza',
            'ci' => 'JUEZ-001', 'telefono' => '70000002',
            'email' => 'elena@demo.com',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $idU_Eval1 = DB::table('usuario')->insertGetId([
            'id_persona' => $idP_Eval1,
            'email' => 'elena@demo.com',
            'password' => Hash::make('password'),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('usuario_rol')->insert([
            'id_usuario' => $idU_Eval1, 'id_rol' => 3, 'id_olimpiada' => $idOlimpiada, 'created_at' => now(), 'updated_at' => now()
        ]);
        DB::table('evaluador_an')->insertGetId([
            'id_usuario' => $idU_Eval1, 'id_area_nivel' => $idAreaNivel, 'estado' => 1, 'created_at' => now(), 'updated_at' => now()
        ]);

        // Juez 2
        $idP_Eval2 = DB::table('persona')->insertGetId([
            'nombre' => 'Claudina', 'apellido' => 'Jueza',
            'ci' => 'JUEZ-002', 'telefono' => '70000003',
            'email' => 'clau@demo.com',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $idU_Eval2 = DB::table('usuario')->insertGetId([
            'id_persona' => $idP_Eval2,
            'email' => 'clau@demo.com',
            'password' => Hash::make('password'),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('usuario_rol')->insert([
            'id_usuario' => $idU_Eval2, 'id_rol' => 3, 'id_olimpiada' => $idOlimpiada, 'created_at' => now(), 'updated_at' => now()
        ]);
        DB::table('evaluador_an')->insertGetId([
            'id_usuario' => $idU_Eval2, 'id_area_nivel' => $idAreaNivel, 'estado' => 1, 'created_at' => now(), 'updated_at' => now()
        ]);

        // 6. Configuración de Medallas (Cupos)
        DB::table('param_medallero')->insert([
            'id_area_nivel' => $idAreaNivel,
            'oro' => 1,
            'plata' => 1,
            'bronce' => 2,
            'mencion' => 5,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // 7. COMPETENCIA (Actualizada)
        $idCompetencia = DB::table('competencia')->insertGetId([
            'id_fase_global' => $idFaseFinal,
            'id_area_nivel' => $idAreaNivel,
            'fecha_inicio' => now(),
            'fecha_fin' => now()->addDays(1),
            'estado_fase' => 'en_proceso',
            'criterio_clasificacion' => 'suma_ponderada',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // 8. EXÁMENES (Actualizados sin duracion_minutos)

        // Examen 1: Teórico con Filtro (30%)
        $idExamenTeorico = DB::table('examen')->insertGetId([
            'id_competencia' => $idCompetencia,
            'nombre' => 'Examen Teórico (Filtro)',
            'ponderacion' => 30.00,
            'maxima_nota' => 100.00,
            'tipo_regla' => 'nota_corte',
            'configuracion_reglas' => json_encode(['nota_minima' => 60]),
            'estado_ejecucion' => 'finalizada',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // Examen 2: Práctico Acumulativo (70%)
        $idExamenPractico = DB::table('examen')->insertGetId([
            'id_competencia' => $idCompetencia,
            'nombre' => 'Resolución de Problemas',
            'ponderacion' => 70.00,
            'maxima_nota' => 100.00,
            'tipo_regla' => null,
            'configuracion_reglas' => null,
            'estado_ejecucion' => 'en_curso',
            'fecha_inicio_real' => now(),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // 9. Competidores y Evaluaciones
        $nombres = ['Juan Perez', 'Maria Gomez', 'Carlos Ruiz', 'Ana Lopez', 'Luis Diaz'];

        foreach ($nombres as $index => $nombreFull) {
            $parts = explode(' ', $nombreFull);
            $i = $index + 1;

            $idP = DB::table('persona')->insertGetId([
                'nombre' => $parts[0], 'apellido' => $parts[1],
                'ci' => "STD-2025-$i", 'telefono' => '60000000',
                'email' => strtolower($parts[0])."@estudiante.com",
                'created_at' => now(), 'updated_at' => now(),
            ]);

            $idComp = DB::table('competidor')->insertGetId([
                'id_persona' => $idP,
                'id_area_nivel' => $idAreaNivel,
                'estado_evaluacion' => 'disponible',
                'created_at' => now(), 'updated_at' => now(),
            ]);

            // Notas Examen 1
            DB::table('evaluacion')->insert([
                'id_competidor' => $idComp,
                'id_examen' => $idExamenTeorico,
                'nota' => rand(50, 100),
                'estado_participacion' => 'presente',
                'resultado_calculado' => 'CLASIFICADO', // Simulado
                'esta_calificado' => 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);

            // Notas Examen 2 (Vacías para probar sala)
            DB::table('evaluacion')->insert([
                'id_competidor' => $idComp,
                'id_examen' => $idExamenPractico,
                'nota' => 0,
                'estado_participacion' => 'presente',
                'bloqueado_por' => null,
                'esta_calificado' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
}
