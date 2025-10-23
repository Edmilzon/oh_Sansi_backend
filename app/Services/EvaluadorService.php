<?php

namespace App\Services;

use App\Mail\UserCredentialsMail;
use App\Models\Persona;
use App\Repositories\EvaluadorRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class EvaluadorService
{
    protected $evaluadorRepository;

    public function __construct(EvaluadorRepository $evaluadorRepository)
    {
        $this->evaluadorRepository = $evaluadorRepository;
    }

    public function createNewEvaluador(array $data): Persona
    {

        return DB::transaction(function () use ($data) {
            // Guardamos la contraseña en texto plano antes de que se encripte
            $plainPassword = $data['password'];

            $persona = $this->evaluadorRepository->createPersona([
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'ci' => $data['ci'],
                'email' => $data['email'],
            ]);

            $usuario = $this->evaluadorRepository->createUsuario([
                'nombre' => $data['email'], // Es mejor usar el email como nombre de usuario para login
                'password' => $data['password'], 
                'rol' => \App\Models\Usuario::ROL_EVALUADOR,
                'id_persona' => $persona->id_persona,
                'id_codigo_evaluador' => null,
                'id_codigo_encargado' => null, 
            ]);


            foreach ($data['areas_niveles'] as $areaNivel) {
                $id_area = $areaNivel['area'];
                foreach ($areaNivel['niveles'] as $id_nivel) {
                    $this->evaluadorRepository->createEvaluador([
                        'id_persona' => $persona->id_persona,
                        'id_area' => $id_area,
                        'id_nivel' => $id_nivel,
                        'activo' => true
                    ]);
                }
            }
            
            // Enviar el correo electrónico con las credenciales
            Mail::to($persona->email)->send(new UserCredentialsMail($persona->nombre, $persona->email, $plainPassword));

            return $persona->load(['usuario', 'evaluador']);
        });
    }
}