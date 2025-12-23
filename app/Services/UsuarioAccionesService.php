<?php

namespace App\Services;

use App\Model\Olimpiada;
use App\Model\Usuario;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UsuarioAccionesService
{
    public function misAcciones(int $userId): array
    {
        return $this->obtenerDetalleCapacidades($userId);
    }

    public function obtenerDetalleCapacidades(int $userId): array
    {
        $usuario = Usuario::with('roles')->find($userId);
        if (!$usuario) return ['error' => 'Usuario no encontrado'];

        $rolesIds = $usuario->roles->pluck('id_rol')->toArray();
        if (empty($rolesIds)) return ['error' => 'Usuario sin roles asignados'];

        $olimpiada = Olimpiada::where('estado', 1)->first();
        if (!$olimpiada) {
            return $this->respuestaVacia($usuario, 'No hay ninguna Olimpiada con estado = 1 (Activa).');
        }

        $cronograma = DB::table('cronograma_fase as cf')
            ->join('fase_global as fg', 'cf.id_fase_global', '=', 'fg.id_fase_global')
            ->where('fg.id_olimpiada', $olimpiada->id_olimpiada)
            ->where('cf.estado', 1)
            ->whereDate('cf.fecha_inicio', '<=', Carbon::now())
            ->whereDate('cf.fecha_fin', '>=', Carbon::now())
            ->select(
                'cf.id_fase_global',
                'fg.nombre as nombre_fase',
                'cf.fecha_inicio',
                'cf.fecha_fin'
            )
            ->first();

        if (!$cronograma) {
            return $this->respuestaVacia($usuario, 'Olimpiada activa encontrada, pero NO hay ninguna fase en el cronograma con estado=1 y fechas vigentes hoy.');
        }

        $acciones = DB::table('accion_sistema as a')
            ->join('rol_accion as ra', 'a.id_accion_sistema', '=', 'ra.id_accion_sistema')
            ->join('configuracion_accion as ca', 'a.id_accion_sistema', '=', 'ca.id_accion_sistema')
            ->whereIn('ra.id_rol', $rolesIds)
            ->where('ra.activo', true)
            ->where('ca.id_fase_global', $cronograma->id_fase_global)
            ->where('ca.habilitada', true)

            ->select('a.codigo', 'a.nombre', 'a.descripcion')
            ->distinct()
            ->get()
            ->toArray();
        return [
            'user_id' => $usuario->id_usuario,
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

    private function respuestaVacia($usuario, $motivo)
    {
        return [
            'user_id' => $usuario->id_usuario,
            'usuario' => $usuario->nombre . ' ' . $usuario->ap_paterno,
            'roles'   => $usuario->roles->pluck('nombre'),
            'debug_estado' => [
                'mensaje_sistema' => $motivo,
                'cronograma_activo' => false
            ],
            'acciones_permitidas' => []
        ];
    }
}
