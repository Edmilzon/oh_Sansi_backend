<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class UserActionService
{
    public function esSuperAdmin(int $userId): bool
    {
        return DB::table('usuario_rol as ur')
            ->join('rol as r', 'ur.id_rol', '=', 'r.id_rol')
            ->where('ur.id_usuario', $userId)
            ->where(function ($query) {
                $query->where('r.id_rol', 1)
                      ->orWhere('r.nombre', 'Administrador');
            })
            ->exists();
    }

    public function can(int $userId, string $accionCodigo): bool
    {
        return DB::table('usuario_rol as ur')
            ->join('rol_accion as ra', 'ur.id_rol', '=', 'ra.id_rol')
            ->join('accion_sistema as a', 'ra.id_accion_sistema', '=', 'a.id_accion_sistema')
            ->where('ur.id_usuario', $userId)
            ->where('a.codigo', $accionCodigo)
            ->where('ra.activo', true)

            ->exists();
    }
}
