<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Persona;
use App\Models\Usuario;
use App\Models\Responsable;
use App\Models\Area;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResponsableAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $personaResponsable = Persona::firstOrCreate(
            ['email' => 'responsable@ohsansi.com'],
            [
                'nombre' => 'Juan',
                'apellido' => 'Perez',
                'ci' => '1234567',
                'telefono' => '77777777',
            ]
        );

        Usuario::firstOrCreate(
            ['id_persona' => $personaResponsable->id_persona],
            [
                'nombre' => 'responsable',
                'password' => Hash::make('Resp1234*'),
                'rol' => Usuario::ROL_RESPONSABLE,
            ]
        );

        $areaMatematicas = Area::where('nombre', 'Matematicas')->first();
        $areaFisica = Area::where('nombre', 'Fisica')->first();

        Responsable::firstOrCreate(['id_persona' => $personaResponsable->id_persona, 'id_area' => $areaMatematicas->id_area], ['fecha_asignacion' => now()]);
        Responsable::firstOrCreate(['id_persona' => $personaResponsable->id_persona, 'id_area' => $areaFisica->id_area], ['fecha_asignacion' => now()]);
    }
}