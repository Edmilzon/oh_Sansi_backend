<?php

namespace App\Repositories;

use App\Model\Usuario;
use App\Model\ResponsableArea;
use App\Model\Roles;
use App\Model\Area;
use App\Model\AreaOlimpiada;
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
            'password' => Hash::make($data['password']), 
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
     * @param int $olimpiadaId
     * @return array
     */
    public function createResponsableAreaRelations(Usuario $usuario, array $areaIds, int $olimpiadaId): array
    {
        $responsableAreas = [];

        foreach ($areaIds as $areaId) {
            $areaOlimpiada = AreaOlimpiada::where('id_area', $areaId)
                                          ->where('id_olimpiada', $olimpiadaId)
                                          ->first();

            if (!$areaOlimpiada) {
                throw new \Exception("La combinación del área ID {$areaId} y la olimpiada ID {$olimpiadaId} no existe.");
            }

            $responsableArea = ResponsableArea::create([
                'id_usuario' => $usuario->id_usuario,
                'id_area_olimpiada' => $areaOlimpiada->id_area_olimpiada,
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
        ->with(['responsableArea.area', 'roles'])->get();
        return $responsables->map(fn($usuario) => $this->formatResponsableData($usuario))->toArray();
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
        ->with(['responsableArea.area', 'roles'])->find($id);

        if (!$usuario) {
            return null;
        }
        return $this->formatResponsableData($usuario);
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
            $query->whereHas('area', fn($q) => $q->where('area.id_area', $areaId));
        })
        ->whereHas('roles', function ($query) {
            $query->where('nombre', 'Responsable Area');
        })->with(['responsableArea.area', 'roles'])->get();
        return $responsables->map(fn($usuario) => $this->formatResponsableData($usuario, false))->toArray();
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
        })->with(['responsableArea.area', 'roles'])->get();
        return $responsables->map(fn($usuario) => $this->formatResponsableData($usuario, false))->toArray();
    }

    /**
     * Encuentra las gestiones (olimpiadas) en las que un responsable ha trabajado, buscado por CI.
     *
     * @param string $ci
     * @return array
     */
    public function findGestionesByCi(string $ci): array
    {
        $usuario = Usuario::where('ci', $ci)
            ->whereHas('roles', function ($query) {
                $query->where('nombre', 'Responsable Area');
            })
            ->with('roles')
            ->first();

        if (!$usuario) {
            return [];
        }

        $olimpiadaIds = $usuario->roles->pluck('pivot.id_olimpiada')->unique()->values();

        $olimpiadas = \App\Model\Olimpiada::whereIn('id_olimpiada', $olimpiadaIds)
            ->get(['id_olimpiada', 'gestion']);

        return $olimpiadas->map(function ($olimpiada) {
            return [
                'Id_olimpiada' => $olimpiada->id_olimpiada,
                'gestion' => $olimpiada->gestion,
            ];
        })->toArray();
    }

    /**
      * Encuentra las áreas asignadas a un responsable por su CI y una gestión específica.
     *
     * @param string $ci
     * @param string $gestion
     * @return array
     */
    public function findAreasByCiAndGestion(string $ci, string $gestion): array
    {
        $usuario = Usuario::where('ci', $ci)
            ->whereHas('roles', function ($query) use ($gestion) {
                $query->where('nombre', 'Responsable Area');
                $query->whereIn('usuario_rol.id_olimpiada', function ($subquery) use ($gestion) {
                    $subquery->select('id_olimpiada')->from('olimpiada')->where('gestion', $gestion);
                });
            })
            ->with(['responsableArea.areaOlimpiada.olimpiada', 'responsableArea.area'])
            ->first();

        if (!$usuario) {
            return [];
        }

        // Filtrar las áreas para que coincidan solo con la gestión solicitada
        $areasDeLaGestion = $usuario->responsableArea->filter(function ($responsableArea) use ($gestion) {
            return $responsableArea->areaOlimpiada && $responsableArea->areaOlimpiada->olimpiada->gestion == $gestion;
        });

        // Formatear la salida como se solicitó
        return $areasDeLaGestion->map(function ($responsableArea) {
            return [
                'id_responsable_area' => $responsableArea->id_responsableArea,
                'Area' => [
                    'Id_area' => $responsableArea->area->id_area,
                    'Nombre' => $responsableArea->area->nombre,
                ]
            ];
        })->values()->toArray();
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
        if (isset($data['password'])) $updateData['password'] = Hash::make($data['password']);
        if (isset($data['telefono'])) $updateData['telefono'] = $data['telefono'];

        $usuario->update($updateData);
        return $usuario->fresh();
    }

    /**
     * Actualiza las relaciones del responsable con las áreas.
     *
     * @param Usuario $usuario
     * @param array $areaIds
     * @param int $olimpiadaId
     * @return void
     */
    public function updateResponsableAreaRelations(Usuario $usuario, array $areaIds, int $olimpiadaId): void
    {
        // Eliminar relaciones existentes
        ResponsableArea::where('id_usuario', $usuario->id_usuario)->delete();
        // Crear nuevas relaciones
        $this->createResponsableAreaRelations($usuario, $areaIds, $olimpiadaId);
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

    /**
     * Formatea los datos de un usuario responsable.
     *
     * @param Usuario $usuario
     * @param bool $includeOlimpiadas
     * @return array
     */
    private function formatResponsableData(Usuario $usuario, bool $includeOlimpiadas = true): array
    {
        $data = [
            'id_usuario' => $usuario->id_usuario,
            'nombre' => $usuario->nombre,
            'apellido' => $usuario->apellido,
            'ci' => $usuario->ci,
            'email' => $usuario->email,
            'telefono' => $usuario->telefono,
            'areas_asignadas' => $usuario->responsableArea->map(function ($ra) {
                return $ra->area ? ['id_area' => $ra->area->id_area, 'nombre_area' => $ra->area->nombre] : null;
            })->filter()->values(),
            'created_at' => $usuario->created_at,
            'updated_at' => $usuario->updated_at,
        ];

        if ($includeOlimpiadas) {
            $data['olimpiadas'] = $usuario->roles->map(function ($role) {
                return ['id_olimpiada' => $role->pivot->id_olimpiada, 'rol' => $role->nombre];
            });
        }

        return $data;
    }

    /**
     * Busca un usuario por su CI.
     *
     * @param string $ci
     * @return Usuario|null
     */
    public function findUsuarioByCi(string $ci): ?Usuario
    {
        return Usuario::where('ci', $ci)->first();
    }
}
