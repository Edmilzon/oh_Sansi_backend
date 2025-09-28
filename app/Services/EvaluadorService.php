<?php

namespace App\Services;

use App\Models\Evaluador;
use App\Models\Persona;
use App\Repositories\EvaluadorRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EvaluadorService
{
    protected $evaluadorRepository;

    public function __construct(EvaluadorRepository $evaluadorRepository)
    {
        $this->evaluadorRepository = $evaluadorRepository;
    }

    public function loginEvaluador(string $data): Evaluador
    {
        $evaluador = $this->evaluadorRepository->loginEvaluador($data['email'], $data['password']);
        return $evaluador;
    }

    public function createNewEvaluador(array $data): Persona
    {
        $codigoEvaluador = $this->evaluadorRepository->findCodigo($data['codigo_evaluador']);

        if (!$codigoEvaluador) {
            throw ValidationException::withMessages(['codigo_evaluador' => 'El código de evaluador no se encontró o no está activo.']);
        }

        return DB::transaction(function () use ($data, $codigoEvaluador) {
            $persona = $this->evaluadorRepository->createPersona([
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'ci' => $data['ci'],
                'fecha_nac' => $data['fecha_nac'],
                'genero' => $data['genero'],
                'telefono' => $data['telefono'],
                'email' => $data['email'],
            ]);

            $usuario = $this->evaluadorRepository->createUsuario([
                'nombre' => $data['username'],
                'password' => $data['password'], 
                'rol' => \App\Models\Usuario::ROL_EVALUADOR,
                'id_persona' => $persona->id_persona,
                'id_codigo_evaluador' => $codigoEvaluador->id_codigo_evaluador,
                'id_codigo_encargado' => null, 
            ]);

            $evaluador = $this->evaluadorRepository->createEvaluador([
                'id_persona' => $persona->id_persona,
                'activo' => true,
            ]);

            return $persona->load(['usuario', 'evaluador']);
        });
    }
}