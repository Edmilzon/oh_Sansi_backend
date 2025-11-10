<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Model\Area;
use App\Model\Nivel;
use App\Model\Olimpiada;
use App\Model\Usuario;
use App\Model\GradoEscolaridad;

class AreasEvaluadoresSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // 1️⃣ Grados escolares
        $grados = [
            ['nombre' => '1ro de Secundaria'],
            ['nombre' => '2do de Secundaria'],
            ['nombre' => '3ro de Secundaria'],
        ];
        GradoEscolaridad::insert(array_map(fn($g)=>array_merge($g,['created_at'=>$now,'updated_at'=>$now]),$grados));

        // 2️⃣ Olimpiada del año actual
        $olimpiada = Olimpiada::where('gestion', date('Y'))->first();
        if (!$olimpiada) {
            $this->command->error('No se encontró olimpiada para el año actual.');
            return;
        }

        // 3️⃣ Áreas
        $areas = Area::all();
        if ($areas->isEmpty()) {
            $this->command->error('No hay áreas. Ejecuta AreasSeeder primero.');
            return;
        }

        // 4️⃣ Niveles
        $niveles = Nivel::all();
        if ($niveles->isEmpty()) {
            $this->command->error('No hay niveles. Ejecuta NivelesSeeder primero.');
            return;
        }

        // 5️⃣ Crear area_nivel según la distribución
        $areaNivelData = [];
        foreach ($areas as $area) {
            if (in_array($area->id_area, [1,2,3])) { // Áreas 1,2,3 → 3 niveles
                for ($i=1; $i<=3; $i++) {
                    $areaNivelData[] = [
                        'id_area' => $area->id_area,
                        'id_nivel' => $i,
                        'id_grado_escolaridad' => $i, // 1ro a 3ro
                        'id_olimpiada' => $olimpiada->id_olimpiada,
                        'activo' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            } else { // Áreas 4,5 → 1 nivel (1ro)
                $areaNivelData[] = [
                    'id_area' => $area->id_area,
                    'id_nivel' => 1,
                    'id_grado_escolaridad' => 1,
                    'id_olimpiada' => $olimpiada->id_olimpiada,
                    'activo' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        DB::table('area_nivel')->insert($areaNivelData);

        $this->command->info("✅ Area_nivel creada según la distribución solicitada.");

        // 6️⃣ Roles
        $rolResp = DB::table('rol')->where('nombre','Responsable Area')->first();
        $rolEval = DB::table('rol')->where('nombre','Evaluador')->first();
        if (!$rolResp || !$rolEval) {
            $this->command->error('No se encontraron roles. Ejecuta RolesSeeder primero.');
            return;
        }

        // 7️⃣ Crear responsables
        $responsables = [
            ['nombre'=>'Resp1','apellido'=>'Sistema','areas'=>[1,2,3]],
            ['nombre'=>'Resp2','apellido'=>'Sistema','areas'=>[4]],
            ['nombre'=>'Resp3','apellido'=>'Sistema','areas'=>[5]],
        ];

        $contadorEval = 1;
        foreach ($responsables as $resp) {
            $usuario = Usuario::create([
                'nombre' => $resp['nombre'],
                'apellido' => $resp['apellido'],
                'ci' => rand(1000000,9999999),
                'email' => strtolower($resp['nombre'].'@ohsansi.com'),
                'password' => Hash::make('responsable123'),
                'telefono' => '7'.rand(1000000,9999999),
            ]);

            // Asignar rol
            DB::table('usuario_rol')->insert([
                'id_usuario'=>$usuario->id_usuario,
                'id_rol'=>$rolResp->id_rol,
                'id_olimpiada'=>$olimpiada->id_olimpiada,
                'created_at'=>$now,
                'updated_at'=>$now,
            ]);

            // Asignar responsable a cada área
            foreach ($resp['areas'] as $id_area) {
                $areaOlimpiada = DB::table('area_olimpiada')
                    ->where('id_area',$id_area)
                    ->where('id_olimpiada',$olimpiada->id_olimpiada)
                    ->first();
                DB::table('responsable_area')->insert([
                    'id_usuario'=>$usuario->id_usuario,
                    'id_area_olimpiada'=>$areaOlimpiada->id_area_olimpiada,
                    'created_at'=>$now,
                    'updated_at'=>$now,
                ]);
            }

            // 8️⃣ Crear evaluadores por área_nivel
            foreach ($resp['areas'] as $id_area) {
                $areaNiveles = DB::table('area_nivel')
                    ->where('id_area',$id_area)
                    ->where('id_olimpiada',$olimpiada->id_olimpiada)
                    ->get();

                foreach ($areaNiveles as $an) {
                    // Distribución de evaluadores por área y nivel según lo que definiste
                    $cantidad = match($id_area){
                        1 => 1, // Área1 → 1 evaluador por nivel
                        2 => ($an->id_nivel==1 ? 2 : 2), // Área2 → 2 + 2
                        3 => ($an->id_nivel==1 ? 2 : 1), // Área3 → 2 + 1
                        4 => 2, // Área4 → 2 evaluadores
                        5 => 1, // Área5 → 1 evaluador
                        default => 1,
                    };

                    for ($i=0;$i<$cantidad;$i++){
                        $eval = Usuario::create([
                            'nombre'=>"Eval{$contadorEval}_A{$id_area}_N{$an->id_nivel}",
                            'apellido'=>'Tester',
                            'ci'=>rand(1000000,9999999),
                            'email'=>strtolower("eval{$contadorEval}_A{$id_area}_N{$an->id_nivel}@ohsansi.com"),
                            'password'=>Hash::make('evaluador123'),
                            'telefono'=>'6'.rand(1000000,9999999),
                        ]);
                        DB::table('usuario_rol')->insert([
                            'id_usuario'=>$eval->id_usuario,
                            'id_rol'=>$rolEval->id_rol,
                            'id_olimpiada'=>$olimpiada->id_olimpiada,
                            'created_at'=>$now,
                            'updated_at'=>$now,
                        ]);

                        DB::table('evaluador_an')->insert([
                            'id_usuario'=>$eval->id_usuario,
                            'id_area_nivel'=>$an->id_area_nivel,
                            'created_at'=>$now,
                            'updated_at'=>$now,
                        ]);
                        $contadorEval++;
                    }
                }
            }
        }

        $this->command->info('🎯 Todos los responsables y evaluadores se crearon correctamente.');
        $this->command->info('🔑 Contraseñas predeterminadas: responsable123 / evaluador123');
    }
}
