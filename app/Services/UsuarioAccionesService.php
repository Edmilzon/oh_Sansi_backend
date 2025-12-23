<?php

namespace App\Services;

use App\Model\Olimpiada;
use App\Model\Usuario;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UsuarioAccionesService
{
    public function obtenerDetalleCapacidades(int $userId): array
    {
        // ------------------------------------------------------------------
        // PASO 0: DATOS DEL USUARIO
        // ------------------------------------------------------------------
        $usuario = Usuario::with('roles')->find($userId);
        if (!$usuario) return ['error' => 'Usuario no encontrado'];

        $rolesIds = $usuario->roles->pluck('id_rol')->toArray();
        if (empty($rolesIds)) return ['error' => 'Usuario sin roles asignados'];

        // ------------------------------------------------------------------
        // PASO 1: ENCONTRAR LA FASE ACTIVA (LA CASCADA)
        // ------------------------------------------------------------------

        // A. Buscar Olimpiada Activa
        $olimpiada = Olimpiada::where('estado', 1)->first();
        if (!$olimpiada) {
            return $this->respuestaVacia($usuario, 'No hay ninguna Olimpiada con estado = 1 (Activa).');
        }

        // B. Buscar el Cronograma Activo vinculado a esta Olimpiada
        // Hacemos un JOIN explícito para asegurar que la fase pertenece a la olimpiada actual
        $cronograma = DB::table('cronograma_fase as cf')
            ->join('fase_global as fg', 'cf.id_fase_global', '=', 'fg.id_fase_global')
            ->where('fg.id_olimpiada', $olimpiada->id_olimpiada) // Filtro de Olimpiada
            ->where('cf.estado', 1)                              // Filtro de Estado del Cronograma (IMPORTANTE)
            ->whereDate('cf.fecha_inicio', '<=', Carbon::now())  // Ya empezó
            ->whereDate('cf.fecha_fin', '>=', Carbon::now())     // No ha terminado
            ->select(
                'cf.id_fase_global',
                'fg.nombre as nombre_fase',
                'cf.fecha_inicio',
                'cf.fecha_fin'
            )
            ->first();

        // Si no encontramos cronograma activo, detenemos todo.
        if (!$cronograma) {
            return $this->respuestaVacia($usuario, 'Olimpiada activa encontrada, pero NO hay ninguna fase en el cronograma con estado=1 y fechas vigentes hoy.');
        }

        // ------------------------------------------------------------------
        // PASO 2: CRUCE DE PERMISOS (INTERSECCIÓN ESTRICTA)
        // ------------------------------------------------------------------
        // Solo traemos acciones que coincidan en:
        // 1. Mis Roles (rol_accion)
        // 2. Configuración de ESTA fase específica (configuracion_accion)

        $acciones = DB::table('accion_sistema as a')
            ->join('rol_accion as ra', 'a.id_accion_sistema', '=', 'ra.id_accion_sistema')
            ->join('configuracion_accion as ca', 'a.id_accion_sistema', '=', 'ca.id_accion_sistema')

            // FILTRO 1: Roles del Usuario
            ->whereIn('ra.id_rol', $rolesIds)
            ->where('ra.activo', true)

            // FILTRO 2: Fase Actual Detectada en Paso 1
            ->where('ca.id_fase_global', $cronograma->id_fase_global)
            ->where('ca.habilitada', true) // El checkbox del admin

            ->select('a.codigo', 'a.nombre', 'a.descripcion')
            ->distinct()
            ->get()
            ->toArray();

        // ------------------------------------------------------------------
        // PASO 3: RETORNO DE ÉXITO
        // ------------------------------------------------------------------
        return [
            'usuario' => $usuario->nombre . ' ' . $usuario->ap_paterno,
            'roles'   => $usuario->roles->pluck('nombre'),
            'debug_estado' => [
                'olimpiada_activa' => $olimpiada->nombre,
                'fase_detectada'   => $cronograma->nombre_fase,
                'cronograma_activo' => true
            ],
            'acciones_permitidas' => $acciones
        ];
    }

    /**
     * Helper para devolver respuesta limpia cuando no hay fases activas
     */
    private function respuestaVacia($usuario, $motivo)
    {
        return [
            'usuario' => $usuario->nombre . ' ' . $usuario->ap_paterno,
            'roles'   => $usuario->roles->pluck('nombre'),
            'debug_estado' => [
                'mensaje_sistema' => $motivo,
                'cronograma_activo' => false
            ],
            'acciones_permitidas' => [] // Array vacío, el frontend oculta todo
        ];
    }
}
