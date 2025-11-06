<?php

namespace App\Services;

use App\Repositories\AuthRepository;
use App\Models\Usuario;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    /**
     * Maneja el proceso de login del usuario.
     *
     * @param array $credentials
     * @return array
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        $email = $credentials['email'];
        $password = $credentials['password'];

        // Verificar credenciales
        $user = $this->authRepository->verifyCredentialsByEmail($email, $password);

        // Crear token de acceso
        $token = $this->authRepository->createToken($user);

        // Obtener datos completos del usuario
        $userData = $this->authRepository->getLoginUserData($user);

        return [
            'message' => 'Login exitoso',
            'token' => $token,
            'token_type' => 'Bearer',
            'data' => $userData,
        ];
    }

    /**
     * Maneja el logout del usuario.
     *
     * @param Usuario $user
     * @return void
     */
    public function logout(Usuario $user): void
    {
        $this->authRepository->revokeAllTokens($user);
    }

    /**
     * Obtiene la información del usuario con sus roles.
     *
     * @param Usuario $user
     * @return array
     */
    public function getUserWithRoles(Usuario $user): array
    {
        return $this->authRepository->getLoginUserData($user);
    }

    /**
     * Verifica si el usuario tiene un rol específico en una olimpiada.
     *
     * @param Usuario $user
     * @param string $rolNombre
     * @param int $idOlimpiada
     * @return bool
     */
    public function userHasRole(Usuario $user, string $rolNombre, int $idOlimpiada): bool
    {
        return $this->authRepository->userHasRole($user, $rolNombre, $idOlimpiada);
    }

    /**
     * Obtiene los roles del usuario para una olimpiada específica.
     *
     * @param Usuario $user
     * @param int $idOlimpiada
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserRolesForOlimpiada(Usuario $user, int $idOlimpiada)
    {
        return $this->authRepository->getUserRolesForOlimpiada($user, $idOlimpiada);
    }

    /**
     * Obtiene todas las olimpiadas donde el usuario tiene roles.
     *
     * @param Usuario $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserOlimpiadas(Usuario $user)
    {
        return $this->authRepository->getUserOlimpiadas($user);
    }

    /**
     * Valida que el usuario tenga acceso a una olimpiada específica.
     *
     * @param Usuario $user
     * @param int $idOlimpiada
     * @return bool
     */
    public function validateOlimpiadaAccess(Usuario $user, int $idOlimpiada): bool
    {
        $olimpiadas = $this->getUserOlimpiadas($user);
        return $olimpiadas->contains('id_olimpiada', $idOlimpiada);
    }

    /**
     * Obtiene información específica del usuario según su rol principal.
     *
     * @param Usuario $user
     * @param int $idOlimpiada
     * @return array
     */
    public function getUserRoleSpecificInfo(Usuario $user, int $idOlimpiada): array
    {
        $roles = $this->getUserRolesForOlimpiada($user, $idOlimpiada);
        $roleInfo = [];

        foreach ($roles as $role) {
            switch ($role->nombre) {
                case 'Administrador':
                    $roleInfo[] = [
                        'rol' => 'Administrador',
                        'permisos' => ['gestion_completa', 'usuarios', 'olimpiadas', 'evaluaciones'],
                        'descripcion' => 'Acceso completo al sistema'
                    ];
                    break;

                case 'Responsable Area':
                    $roleInfo[] = [
                        'rol' => 'Responsable Area',
                        'permisos' => ['gestion_area', 'competidores', 'evaluaciones_area'],
                        'descripcion' => 'Gestión de área específica'
                    ];
                    break;

                case 'Evaluador':
                    $roleInfo[] = [
                        'rol' => 'Evaluador',
                        'permisos' => ['evaluar', 'ver_competidores'],
                        'descripcion' => 'Evaluación de competidores'
                    ];
                    break;
            }
        }

        return $roleInfo;
    }

    /**
     * Genera un resumen de permisos del usuario.
     *
     * @param Usuario $user
     * @param int $idOlimpiada
     * @return array
     */
    public function getUserPermissions(Usuario $user, int $idOlimpiada): array
    {
        $roleInfo = $this->getUserRoleSpecificInfo($user, $idOlimpiada);
        $permissions = [];

        foreach ($roleInfo as $role) {
            $permissions = array_merge($permissions, $role['permisos']);
        }

        return [
            'roles' => $roleInfo,
            'permisos' => array_unique($permissions),
            'es_administrador' => $this->userHasRole($user, 'Administrador', $idOlimpiada),
            'es_responsable_area' => $this->userHasRole($user, 'Responsable Area', $idOlimpiada),
            'es_evaluador' => $this->userHasRole($user, 'Evaluador', $idOlimpiada),
        ];
    }

    /**
     * Obtiene información de un usuario por su CI.
     *
     * @param string $ci
     * @return array|null
     */
    public function getUserByCi(string $ci): ?array
    {
        $user = $this->authRepository->findByCi($ci);
        
        if (!$user) {
            return null;
        }

        return $this->authRepository->getLoginUserData($user);
    }
}
