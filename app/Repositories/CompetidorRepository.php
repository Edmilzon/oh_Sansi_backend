<?php

namespace App\Repositories;

use App\Model\Competidor;
use App\Model\Persona;
use App\Model\Institucion;
use App\Model\ArchivoCsv;
use App\Model\Area;
use App\Model\Nivel;
use App\Model\AreaNivel;
use App\Model\Olimpiada;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompetidorRepository
{
    public function crearInstitucion($nombre)
    {
        return Institucion::firstOrCreate(
            ['nombre' => trim($nombre)],
            ['nombre' => trim($nombre)]
        );
    }

    public function buscarOCrearPersona($datos)
    {
        return Persona::firstOrCreate(
            ['ci' => $datos['ci']],
            [
                'nombre' => $datos['nombre'],
                'apellido' => $datos['apellido'],
                'genero' => $datos['genero'],
                'email' => $datos['email'],
                'telefono' => $datos['telefono'] ?? null,
            ]
        );
    }

    public function obtenerAreaNivel($nombreArea, $nombreNivel, $idOlimpiada)
    {
        $area = Area::where('nombre', 'like', '%' . trim($nombreArea) . '%')->first();
        if (!$area) {
            return null;
        }

        $nivel = Nivel::where('nombre', 'like', '%' . trim($nombreNivel) . '%')->first();
        if (!$nivel) {
            return null;
        }

        return AreaNivel::where('id_area', $area->id_area)
            ->where('id_nivel', $nivel->id_nivel)
            ->where('id_olimpiada', $idOlimpiada)
            ->where('activo', true)
            ->first();
    }

    public function crearArchivoCsv($nombreArchivo, $idOlimpiada)
    {
        return ArchivoCsv::create([
            'nombre' => $nombreArchivo,
            'fecha' => now()->toDateString(),
            'id_olimpiada' => $idOlimpiada,
        ]);
    }

    public function crearCompetidor($datos, $idInstitucion, $idAreaNivel, $idArchivoCsv, $idPersona)
    {
        return Competidor::create([
            'grado_escolar' => $datos['grado_escolar'],
            'departamento' => $datos['departamento'],
            'contacto_tutor' => $datos['contacto_tutor'] ?? null,
            'id_institucion' => $idInstitucion,
            'id_area_nivel' => $idAreaNivel,
            'id_archivo_csv' => $idArchivoCsv,
            'id_persona' => $idPersona,
        ]);
    }

    public function competidorExiste($ci, $idAreaNivel)
    {
        return Competidor::whereHas('persona', function($query) use ($ci) {
            $query->where('ci', $ci);
        })->where('id_area_nivel', $idAreaNivel)->exists();
    }

    public function procesarTransaccion(callable $callback)
    {
        return DB::transaction($callback);
    }

    public function findWithRelations($id) {
        return Competidor::with(['persona', 'institucion'])->find($id);
    }

    public function getAllCompetidores(){
        return Competidor::all();
    }

    public function getCompetidoresByAreaIds(array $areaIds)
    {
        return Competidor::with([
                'persona', 
                'institucion', 
                'areaNivel.area', 
                'areaNivel.nivel'
            ])->whereIn('id_area', $areaIds)->get();
    }
}