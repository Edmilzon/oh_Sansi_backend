<?php

namespace App\Services;

use App\Repositories\RolAccionRepository;
use App\Services\UsuarioAccionesService;
use App\Services\UserActionService;
use App\Model\AccionSistema;
use App\Model\Rol;
use App\Events\MisAccionesActualizadas;
use Illuminate\Support\Facades\DB;
use Exception;

class RolAccionService
{
    public function __construct(
        protected RolAccionRepository $repo,
        protected UserActionService $permisoCacheService,
        protected UsuarioAccionesService $usuarioAccionesService
    ) {}

    public function obtenerMatrizGlobal(): array
    {
        $roles = Rol::all();

        if ($roles->isEmpty()) {
            return [];
        }

        $this->sincronizarGlobal($roles);

        $permisos = $this->repo->getAllWithRelations();

        return $roles->map(function ($rol) use ($permisos) {
            $susPermisos = $permisos->where('id_rol', $rol->id_rol);

            return [
                'rol' => [
                    'id_rol' => $rol->id_rol,
                    'nombre' => $rol->nombre,
                ],
                'acciones' => $susPermisos->values()->map(function ($p) {
                    return [
                        'id_rol_accion'     => $p->id_rol_accion,
                        'id_accion_sistema' => $p->id_accion_sistema,
                        'codigo'            => $p->accionSistema->codigo,
                        'nombre'            => $p->accionSistema->nombre,
                        'descripcion'       => $p->accionSistema->descripcion,
                        'activo'            => (bool) $p->activo,
                    ];
                })->toArray()
            ];
        })->values()->toArray();
    }

    public function updateGlobal(array $datos)
    {
        return DB::transaction(function () use ($datos) {
            $rolesAfectados = [];

            foreach ($datos as $itemRol) {
                $idRol = $itemRol['id_rol'];
                $rolesAfectados[] = $idRol;

                if (isset($itemRol['acciones']) && is_array($itemRol['acciones'])) {
                    foreach ($itemRol['acciones'] as $accion) {
                        $this->repo->updateOrCreate(
                            [
                                'id_rol' => $idRol,
                                'id_accion_sistema' => $accion['id_accion_sistema']
                            ],
                            [
                                'activo' => $accion['activo']
                            ]
                        );
                    }
                }

            }

            $this->notificarCambiosEnTiempoReal(array_unique($rolesAfectados));

            return true;
        });
    }

    protected function notificarCambiosEnTiempoReal(array $rolesIds)
    {
        if (empty($rolesIds)) return;

        $userIds = DB::table('usuario_rol')
            ->join('usuario', 'usuario.id_usuario', '=', 'usuario_rol.id_usuario')
            ->whereIn('usuario_rol.id_rol', $rolesIds)
            ->where('usuario_rol.estado', 'AC')
            ->where('usuario.estado', 'AC')
            ->pluck('usuario.id_usuario')
            ->unique();

        foreach ($userIds as $userId) {
            try {
                $nuevasAcciones = $this->usuarioAccionesService->misAcciones($userId);
                broadcast(new MisAccionesActualizadas($userId, $nuevasAcciones));

            } catch (Exception $e) {
                // Loguear error silenciosamente para no interrumpir el flujo principal
                // Log::error("Error enviando socket a usuario $userId: " . $e->getMessage());
            }
        }
    }

    private function sincronizarGlobal($roles): void
    {
        $acciones = AccionSistema::all();

        if ($acciones->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($roles, $acciones) {
            foreach ($roles as $rol) {
                foreach ($acciones as $accion) {
                    $this->repo->firstOrCreate(
                        [
                            'id_rol' => $rol->id_rol,
                            'id_accion_sistema' => $accion->id_accion_sistema
                        ],
                        [
                            'activo' => false
                        ]
                    );
                }
            }
        });
    }
}
