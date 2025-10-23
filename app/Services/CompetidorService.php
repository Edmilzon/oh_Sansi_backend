<?php

namespace App\Services;

use App\Models\Persona;
use App\Repositories\CompetidorRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompetidorService
{
    protected $competidorRepository;

    public function __construct(CompetidorRepository $competidorRepository)
    {
        $this->competidorRepository = $competidorRepository;
    }

    public function createNewCompetidor(array $data): Persona
    {

        return DB::transaction(function () use ($data) {
            $persona = $this->competidorRepository->createPersona([
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'ci' => $data['ci'],
                'fecha_nac' => $data['fecha_nac'],
                'genero' => $data['genero'],
                'telefono' => $data['telefono'],
                'email' => $data['email'],
            ]);

            $competidor = $this->competidorRepository->createCompetidor([
                'id_persona' => $persona->id_persona,
                'grado_escolar' => $data['grado_escolar'] ?? null,        
                'departamento' => $data['departamento'] ?? null,
                'nombre_tutor' => $data['nombre_tutor'] ?? null,     
                'contacto_tutor' => $data['contacto_tutor'] ?? null,
                'contacto_emergencia' => $data['contacto_emergencia'] ?? null,
                'id_institucion' => $data['id_institucion'] ?? null,
                'id_area' => $data['id_area'] ?? null,
                'id_nivel' => $data['id_nivel'] ?? null,
                /*'id_archivo_csv' => $data['id_archivo_csv'] ?? null,*/         
            ]);

            return $persona->load(['competidor']);
        });
    }
}