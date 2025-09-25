<?php

namespace App\Services;

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

    public function createNewEvaluador(array $data)
    {
        // La validación del código ya se hace en el Controller con la regla 'exists'.
        // 1. Obtener el modelo del código de evaluador para usar su ID.
        $codigoEvaluador = $this->evaluadorRepository->findCodigo($data['codigo_evaluador']);

        // 2. Usar una transacción para asegurar la integridad de los datos.
        return DB::transaction(function () use ($data, $codigoEvaluador) {
            // 2.1. Crear la Persona
            $persona = $this->evaluadorRepository->createPersona([
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'ci' => $data['ci'],
                'fecha_nac' => $data['fecha_nac'],
                'genero' => $data['genero'],
                'telefono' => $data['telefono'],
                'email' => $data['email'],
            ]);

            // 2.2. Crear el Usuario
            $usuario = $this->evaluadorRepository->createUsuario([
                'nombre' => $data['username'],
                'password' => $data['password'], // El mutator del modelo se encargará de hashearlo
                'rol' => 'evaluador',
                'id_persona' => $persona->id_persona,
                'id_codigo_evaluador' => $codigoEvaluador->id_codigo_evaluador,
            ]);

            // 2.3. Crear el registro de Evaluador
            $evaluador = $this->evaluadorRepository->createEvaluador([
                'id_persona' => $persona->id_persona,
                'activo' => true,
            ]);

            return $persona->load(['usuario', 'evaluador']);
        });
    }
}