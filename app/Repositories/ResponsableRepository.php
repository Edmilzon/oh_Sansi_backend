<?php

namespace App\Repositories;

use App\Model\Usuario;
use App\Model\ResponsableArea;
use App\Model\Roles;
use App\Model\Area;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ResponsableRepository
{
    /**
     * Crea un nuevo usuario.
     *
     * @param array $data
     * @return Usuario
     */
    public function createUsuario(array $data): Usuario
    {
        $usuarioData = [
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'ci' => $data['ci'],
            'email' => $data['email'],
            'password' => $data['password'], 
            'telefono' => $data['telefono'] ?? null,
        ];

        return Usuario::create($usuarioData);
    }

    /**
     * Asigna el rol de "Responsable Area" al usuario.
     *
     * @param Usuario $usuario
     * @param int $olimpiadaId
     * @return void
     */
    public function assignResponsableRole(Usuario $usuario, int $olimpiadaId): void
    {
        $rolResponsable = Roles::where('nombre', 'Responsable Area')->first();
        
        if (!$rolResponsable) {
            throw new \Exception('El rol "Responsable Area" no existe en el sistema');
        }

        $usuario->asignarRol('Responsable Area', $olimpiadaId);
    }

    /**
     * Crea las relaciones entre el responsable y las áreas.
     *
     * @param Usuario $usuario
     * @param array $areaIds
     * @return array
     */
    public function createResponsableAreaRelations(Usuario $usuario, array $areaIds): array
    {
        $responsableAreas = [];

        foreach ($areaIds as $areaId) {
            $responsableArea = ResponsableArea::create([
                'id_usuario' => $usuario->id_usuario,
                'id_area' => $areaId,
            ]);

            $responsableAreas[] = $responsableArea->load('area');
        }

        return $responsableAreas;
    }

    /**
     * Obtiene todos los responsables con sus áreas asignadas.
     *
     * @return array
     */
    public function getAllResponsablesWithAreas(): array
    {
        $responsables = Usuario::whereHas('roles', function ($query) {
            $query->where('nombre', 'Responsable Area');
        })
        ->with(['responsableArea.area', 'roles'])
        ->get();

        return $responsables->map(function ($usuario) {
            return [
                'id_usuario' => $usuario->id_usuario,
                'nombre' => $usuario->nombre,
                'apellido' => $usuario->apellido,
                'ci' => $usuario->ci,
                'email' => $usuario->email,
                'telefono' => $usuario->telefono,
                'areas_asignadas' => $usuario->responsableArea->map(function ($ra) {
                    return [
                        'id_area' => $ra->area->id_area,
                        'nombre_area' => $ra->area->nombre
                    ];
                }),
                'olimpiadas' => $usuario->roles->map(function ($role) {
                    return [
                        'id_olimpiada' => $role->pivot->id_olimpiada,
                        'rol' => $role->nombre
                    ];
                }),
                'created_at' => $usuario->created_at,
                'updated_at' => $usuario->updated_at
            ];
        })->toArray();
    }

    /**
     * Obtiene un responsable específico por ID con sus áreas.
     *
     * @param int $id
     * @return array|null
     */
    public function getResponsableByIdWithAreas(int $id): ?array
    {
        $usuario = Usuario::whereHas('roles', function ($query) {
            $query->where('nombre', 'Responsable Area');
        })
        ->with(['responsableArea.area', 'roles'])
        ->find($id);

        if (!$usuario) {
            return null;
        }

        return [
            'id_usuario' => $usuario->id_usuario,
            'nombre' => $usuario->nombre,
            'apellido' => $usuario->apellido,
            'ci' => $usuario->ci,
            'email' => $usuario->email,
            'telefono' => $usuario->telefono,
            'areas_asignadas' => $usuario->responsableArea->map(function ($ra) {
                return [
                    'id_area' => $ra->area->id_area,
                    'nombre_area' => $ra->area->nombre
                ];
            }),
            'olimpiadas' => $usuario->roles->map(function ($role) {
                return [
                    'id_olimpiada' => $role->pivot->id_olimpiada,
                    'rol' => $role->nombre
                ];
            }),
            'created_at' => $usuario->created_at,
            'updated_at' => $usuario->updated_at
        ];
    }

    /**
     * Obtiene responsables por área específica.
     *
     * @param int $areaId
     * @return array
     */
    public function getResponsablesByArea(int $areaId): array
    {
        $responsables = Usuario::whereHas('responsableArea', function ($query) use ($areaId) {
            $query->where('id_area', $areaId);
        })
        ->whereHas('roles', function ($query) {
            $query->where('nombre', 'Responsable Area');
        })
        ->with(['responsableArea.area', 'roles'])
        ->get();

        return $responsables->map(function ($usuario) {
            return [
                'id_usuario' => $usuario->id_usuario,
                'nombre' => $usuario->nombre,
                'apellido' => $usuario->apellido,
                'ci' => $usuario->ci,
                'email' => $usuario->email,
                'telefono' => $usuario->telefono,
                'areas_asignadas' => $usuario->responsableArea->map(function ($ra) {
                    return [
                        'id_area' => $ra->area->id_area,
                        'nombre_area' => $ra->area->nombre
                    ];
                })
            ];
        })->toArray();
    }

    /**
     * Obtiene responsables por olimpiada específica.
     *
     * @param int $olimpiadaId
     * @return array
     */
    public function getResponsablesByOlimpiada(int $olimpiadaId): array
    {
        $responsables = Usuario::whereHas('roles', function ($query) use ($olimpiadaId) {
            $query->where('nombre', 'Responsable Area')
                  ->where('usuario_rol.id_olimpiada', $olimpiadaId);
        })
        ->with(['responsableArea.area', 'roles'])
        ->get();

        return $responsables->map(function ($usuario) {
            return [
                'id_usuario' => $usuario->id_usuario,
                'nombre' => $usuario->nombre,
                'apellido' => $usuario->apellido,
                'ci' => $usuario->ci,
                'email' => $usuario->email,
                'telefono' => $usuario->telefono,
                'areas_asignadas' => $usuario->responsableArea->map(function ($ra) {
                    return [
                        'id_area' => $ra->area->id_area,
                        'nombre_area' => $ra->area->nombre
                    ];
                })
            ];
        })->toArray();
    }

    /**
     * Actualiza un usuario existente.
     *
     * @param int $id
     * @param array $data
     * @return Usuario
     */
    public function updateUsuario(int $id, array $data): Usuario
    {
        $usuario = Usuario::findOrFail($id);

        $updateData = [];
        if (isset($data['nombre'])) $updateData['nombre'] = $data['nombre'];
        if (isset($data['apellido'])) $updateData['apellido'] = $data['apellido'];
        if (isset($data['ci'])) $updateData['ci'] = $data['ci'];
        if (isset($data['email'])) $updateData['email'] = $data['email'];
        if (isset($data['password'])) $updateData['password'] = $data['password'];
        if (isset($data['telefono'])) $updateData['telefono'] = $data['telefono'];

        $usuario->update($updateData);
        return $usuario->fresh();
    }

    /**
     * Actualiza las relaciones del responsable con las áreas.
     *
     * @param Usuario $usuario
     * @param array $areaIds
     * @return void
     */
    public function updateResponsableAreaRelations(Usuario $usuario, array $areaIds): void
    {
        // Eliminar relaciones existentes
        ResponsableArea::where('id_usuario', $usuario->id_usuario)->delete();

        // Crear nuevas relaciones
        $this->createResponsableAreaRelations($usuario, $areaIds);
    }

    /**
     * Elimina un responsable y todas sus relaciones.
     *
     * @param int $id
     * @return bool
     */
    public function deleteResponsable(int $id): bool
    {
        $usuario = Usuario::find($id);
        
        if (!$usuario) {
            return false;
        }

        // Eliminar relaciones con áreas
        ResponsableArea::where('id_usuario', $id)->delete();

        // Eliminar relaciones con roles
        $usuario->roles()->detach();

        // Eliminar usuario
        return $usuario->delete();
    }
}
