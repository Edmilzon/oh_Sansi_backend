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

        if (Persona::where('ci', $data['ci'])->exists()) {
        throw ValidationException::withMessages([
            'ci' => 'El CI ya existe'
            ]);
        }

        if (Persona::where('telefono', $data['telefono'])->exists()) {
            throw ValidationException::withMessages([
                'telefono' => 'El teléfono ya existe'
            ]);
        }
        if (Persona::where('email', $data['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'El email ya existe'
            ]);
        }

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
                'contacto_tutor' => $data['contacto_tutor'] ?? null,
                'contacto_emergencia' => $data['contacto_emergencia'] ?? null,
                'id_institucion' => $data['id_institucion'] ?? null,
                'id_area' => $data['id_area'] ?? null,
                'id_nivel' => $data['id_nivel'] ?? null,          
            ]);

            return $persona->load(['competidor']);
        });
    }
}