<?php

namespace App\Services;

use App\Mail\UserCredentialsMail;
use App\Repositories\ResponsableRepository;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class ResponsableService {

    protected $responsableRepository;

    public function __construct(ResponsableRepository $responsableRepository){
        $this->responsableRepository = $responsableRepository;
    }

    public function getResponsableList(){
        return $this->responsableRepository->getAllResponsables();
    }

    public function createNewResponsable(array $data){
        return DB::transaction(function () use ($data)
        {
            $plainPassword = $data['password'];

            $persona = $this->responsableRepository->createPersona([
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'ci' => $data['ci'],
                'email' => $data['email'],
            ]);

            $usuario = $this->responsableRepository->createUsuario([
                'nombre' => $data['email'],
                'password' => $data['password'],
                'rol' => \App\Models\Usuario::ROL_RESPONSABLE,
                'id_persona' => $persona->id_persona,
                'id_codigo_evaluador' => null,
                'id_codigo_encargado' => null,
            ]);

            foreach ($data['areas'] as $id_area) {
                $this->responsableRepository->createResponsable([
                    'id_persona' => $persona->id_persona,
                    'activo' => true,
                    'id_area' => $id_area,
                    'fecha_asignacion' => now()
                ]);
            }

            // Enviar el correo electrónico con las credenciales
            Mail::to($persona->email)->send(new UserCredentialsMail($persona->nombre, $persona->email, $plainPassword));

            return $persona->load(['usuario', 'responsableArea']);

        });
    }
}