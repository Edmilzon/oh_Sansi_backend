<?php

namespace App\Services;

use App\Repositories\RolAccionRepository;
use App\Model\AccionSistema;
use App\Model\Rol;
use Illuminate\Support\Facades\DB;
use App\Services\UserActionService;

class RolAccionService
{
    public function __construct(
        protected RolAccionRepository $repo,
        protected UserActionService $permisoCacheService
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
        })->toArray();
    }

    public function actualizarMatrizGlobal(int $userIdAdmin, array $listaRoles): void
    {
        DB::transaction(function () use ($listaRoles) {

            foreach ($listaRoles as $rolData) {
                $idRol = $rolData['id_rol'];

                if (isset($rolData['acciones']) && is_array($rolData['acciones'])) {
                    foreach ($rolData['acciones'] as $accion) {
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

                // Opcional: Aquí podrías invalidar caché específica si fuera necesario
                // $this->permisoCacheService->clearCachePorRol($idRol);
            }
        });

        // Opcional: Limpieza general de caché de permisos si la política es estricta
        // Cache::tags(['permisos'])->flush();
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
