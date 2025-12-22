<?php

namespace App\Services;

use App\Model\Usuario;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserActionService
{
    protected const CACHE_TTL = 3600;

    public function can(int $userId, string $codigoAccion): bool
    {
        $permisos = $this->getPermisosUsuario($userId);
        return in_array($codigoAccion, $permisos);
    }

    public function getPermisosUsuario(int $userId): array
    {
        return Cache::remember("user_perms_{$userId}", self::CACHE_TTL, function () use ($userId) {
            return DB::table('accion_sistema as a')
                ->join('rol_accion as ra', 'a.id_accion_sistema', '=', 'ra.id_accion_sistema')
                ->join('usuario_rol as ur', 'ra.id_rol', '=', 'ur.id_rol')
                ->where('ur.id_usuario', $userId)
                ->where('ra.activo', true)
                ->pluck('a.codigo')
                ->unique()
                ->values()
                ->toArray();
        });
    }

    public function clearCache(int $userId): void
    {
        Cache::forget("user_perms_{$userId}");
    }
}
