<?php

namespace App\Repositories;

use App\Model\Usuario;

class UsuarioRepository
{
    public function findByEmail(string $email): ?Usuario
    {
        return Usuario::where('email', $email)->with('roles')->first();
    }

    public function findByCiWithDetails(string $ci): ?Usuario
    {
        return Usuario::whereHas('persona', function ($q) use ($ci) {

                $q->where('ci', $ci);
            })
            ->with([
                'persona',
                'roles',

                'responsableAreas.areaOlimpiada.area',
                'responsableAreas.areaOlimpiada.olimpiada',

                'evaluadoresAn.areaNivel.areaOlimpiada.area',
                'evaluadoresAn.areaNivel.nivel',
                'evaluadoresAn.areaNivel.gradosEscolaridad',
            ])->first();
    }

    /**
     * Verifica si un usuario tiene un rol específico asignado.
     * Soporta multi-rol (busca si AL MENOS uno de sus roles coincide).
     */
    public function tieneRol(int $userId, string $nombreRol): bool
    {
        return Usuario::where('id_usuario', $userId)
            ->whereHas('roles', function ($query) use ($nombreRol) {
                $query->where('nombre', $nombreRol);
            })
            ->exists();
    }
}
