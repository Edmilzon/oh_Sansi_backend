<?php

namespace App\Repositories;

use App\Model\Usuario;
use App\Model\Roles;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthRepository
{
    /**
     * Busca un usuario por CI.
     *
     * @param string $ci
     * @return Usuario|null
     */
    public function findByCi(string $ci): ?Usuario
    {
        return Usuario::where('ci', $ci)->first();
    }

    /**
     * Verifica las credenciales del usuario.
     *
     * @param string $ci
     * @param string $password
     * @return Usuario|null
     * @throws ValidationException
     */
    public function verifyCredentials(string $ci, string $password): ?Usuario
    {
        $user = $this->findByCi($ci);

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'ci' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        return $user;
    }

    /**
     * Obtiene un usuario con sus roles y olimpiadas.
     *
     * @param Usuario $user
     * @return Usuario
     */
    public function getUserWithRoles(Usuario $user): Usuario
    {
        return $user->load('roles');
    }

    /**
     * Obtiene los roles de un usuario para una olimpiada específica.
     *
     * @param Usuario $user
     * @param int $idOlimpiada
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserRolesForOlimpiada(Usuario $user, int $idOlimpiada)
    {
        return $user->roles()
            ->wherePivot('id_olimpiada', $idOlimpiada)
            ->get();
    }

    /**
     * Verifica si un usuario tiene un rol específico en una olimpiada.
     *
     * @param Usuario $user
     * @param string $rolNombre
     * @param int $idOlimpiada
     * @return bool
     */
    public function userHasRole(Usuario $user, string $rolNombre, int $idOlimpiada): bool
    {
        return $user->roles()
            ->where('nombre', $rolNombre)
            ->wherePivot('id_olimpiada', $idOlimpiada)
            ->exists();
    }

    /**
     * Obtiene todas las olimpiadas donde el usuario tiene roles asignados.
     *
     * @param Usuario $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserOlimpiadas(Usuario $user)
    {
        return $user->roles()
            ->get()
            ->pluck('pivot.id_olimpiada')
            ->unique()
            ->map(function ($idOlimpiada) {
                return \App\Model\Olimpiada::find($idOlimpiada);
            })
            ->filter();
    }

    /**
     * Crea un token de acceso para el usuario.
     *
     * @param Usuario $user
     * @param string $tokenName
     * @return string
     */
    public function createToken(Usuario $user, string $tokenName = 'auth-token'): string
    {
        return $user->createToken($tokenName)->plainTextToken;
    }

    /**
     * Revoca todos los tokens del usuario.
     *
     * @param Usuario $user
     * @return void
     */
    public function revokeAllTokens(Usuario $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Obtiene información completa del usuario para el login.
     *
     * @param Usuario $user
     * @return array
     */
    public function getLoginUserData(Usuario $user): array
    {
        $userWithRoles = $this->getUserWithRoles($user);
        $olimpiadas = $this->getUserOlimpiadas($user);

        return [
            'user' => [
                'id' => $userWithRoles->id_usuario,
                'nombre' => $userWithRoles->nombre,
                'apellido' => $userWithRoles->apellido,
                'ci' => $userWithRoles->ci,
                'email' => $userWithRoles->email,
                'telefono' => $userWithRoles->telefono,
            ],
            'roles' => $userWithRoles->roles->map(function ($role) {
                return [
                    'id' => $role->id_rol,
                    'nombre' => $role->nombre,
                    'olimpiada_id' => $role->pivot->id_olimpiada,
                ];
            }),
            'olimpiadas' => $olimpiadas->map(function ($olimpiada) {
                return [
                    'id' => $olimpiada->id_olimpiada,
                    'nombre' => $olimpiada->nombre,
                    'gestion' => $olimpiada->gestion,
                ];
            }),
        ];
    }
}
