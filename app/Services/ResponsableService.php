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

            // 1. Crear el usuario
            $usuario = $this->responsableRepository->createUsuario([
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'ci' => $data['ci'],
                'email' => $data['email'],
                'password' => $data['password'],
                'telefono' => $data['telefono'] ?? null,
            ]);

            // 2. Asignar el rol de "Responsable Area" al usuario para la olimpiada especificada
            // Asegúrate de que $data['id_olimpiada'] venga en la solicitud y esté validado.
            $usuario->asignarRol('Responsable Area', $data['id_olimpiada']);

            // 3. Asociar al usuario con sus areas/niveles
            // La tabla 'responsable_area' parece asociar un usuario a un 'id_area_nivel'
            // Tu request actual envía 'areas', que parece ser un array de 'id_area'.
            // Esto necesita ser ajustado según la lógica de negocio.
            // Por ejemplo, si se asocia a un 'id_area_nivel':
            foreach ($data['areas_niveles'] as $id_area_nivel) {
                $this->responsableRepository->createResponsable([
                    'id_usuario' => $usuario->id_usuario,
                    'id_area_nivel' => $id_area_nivel,
                ]);
            }

            // Enviar el correo electrónico con las credenciales
            Mail::to($usuario->email)->send(new UserCredentialsMail($usuario->nombre, $usuario->email, $plainPassword));

            return $usuario->load(['roles', 'responsableArea']);

        });
    }
}