<?php

namespace App\Repositories;

use App\Model\Usuario;
use App\Model\Persona;
use App\Model\Rol;
use App\Model\EvaluadorAn;
use App\Model\Examen;
use App\Model\UsuarioRol;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Exception;

class EvaluadorRepository
{

    public function obtenerExamenesActivosPorJuez(int $userId): Collection
    {
        return Examen::query()
            ->with([
                'competencia.areaNivel.nivel',
                'competencia.areaNivel.areaOlimpiada.area'
            ])

            ->where('estado_ejecucion', 'en_curso')
            ->whereHas('competencia', function ($q) use ($userId) {
                $q->where('estado_fase', 'en_proceso')
                    ->whereHas('areaNivel', function ($q2) use ($userId) {
                        $q2->whereHas('evaluadores', function ($q3) use ($userId) {
                            $q3->where('id_usuario', $userId)
                                ->where('estado', 1);
                        });
                    });
            })
            ->orderByDesc('fecha_inicio_real')
            ->get();
    }

    public function esJuezDelExamen(int $userId, int $examenId): bool
    {
        return Examen::where('id_examen', $examenId)
            ->whereHas('competencia.areaNivel.evaluadores', function ($q) use ($userId) {
                $q->where('id_usuario', $userId)
                    ->where('estado', 1);
            })->exists();
    }

    public function findOrCreatePersona(array $data): Persona
    {
        return Persona::updateOrCreate(
            ['ci' => $data['ci']],
            [
                'nombre'   => $data['nombre'],
                'apellido' => $data['apellido'],
                'email'    => $data['email'],
                'telefono' => $data['telefono'] ?? null,
            ]
        );
    }

    public function createUsuario(Persona $persona, array $data): Usuario
    {
        return Usuario::create([
            'id_persona' => $persona->id_persona,
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
        ]);
    }

    public function assignEvaluadorRole(Usuario $usuario, int $idOlimpiada): void
    {
        $rol = Rol::where('nombre', 'Evaluador')->first();

        if (!$rol) {
            throw new Exception("El rol 'Evaluador' no existe en la base de datos.");
        }

        $existe = $usuario->roles()
            ->where('usuario_rol.id_rol', $rol->id_rol)
            ->wherePivot('id_olimpiada', $idOlimpiada)
            ->exists();

        if (!$existe) {
            $usuario->roles()->attach($rol->id_rol, ['id_olimpiada' => $idOlimpiada]);
        }
    }

    public function syncEvaluadorAreas(Usuario $usuario, array $areaNivelIds): void
    {
        foreach ($areaNivelIds as $idAreaNivel) {
            EvaluadorAn::firstOrCreate([
                'id_usuario'    => $usuario->id_usuario,
                'id_area_nivel' => $idAreaNivel
            ], [
                'estado' => 1
            ]);
        }
    }

    public function getById(int $id): ?array
    {
        $usuario = Usuario::with([
            'persona',
            'evaluadoresAn.areaNivel.areaOlimpiada.area',
            'evaluadoresAn.areaNivel.nivel',
            'evaluadoresAn.areaNivel.areaOlimpiada.olimpiada'
        ])->find($id);

        if (!$usuario) {
            return null;
        }

        return $this->mapToLegacyJson($usuario);
    }

    private function mapToLegacyJson(Usuario $usuario): array
    {
        return [
            'id_usuario' => $usuario->id_usuario,
            'nombre'     => $usuario->persona->nombre ?? '',
            'apellido'   => $usuario->persona->apellido ?? '',
            'ci'         => $usuario->persona->ci ?? '',
            'telefono'   => $usuario->persona->telefono ?? '',
            'email'      => $usuario->email,
            'activo'     => (bool) $usuario->estado,

            'areas_asignadas' => $usuario->evaluadoresAn->map(function($ean) {
                $areaNivel      = $ean->areaNivel;
                $areaOlimpiada  = $areaNivel->areaOlimpiada ?? null;
                $nivel          = $areaNivel->nivel ?? null;

                $nombreArea     = $areaOlimpiada->area->nombre ?? 'Sin Área';
                $nombreNivel    = $nivel->nombre ?? 'Sin Nivel';

                return [
                    'id_evaluador_an'  => $ean->id_evaluador_an,
                    'id_area_olimpiada'=> $areaNivel->id_area_olimpiada ?? null,
                    'id_area_nivel'    => $ean->id_area_nivel,
                    'id_nivel'         => $areaNivel->id_nivel ?? null,
                    'area'             => $nombreArea,
                    'nivel'            => $nombreNivel,
                    'gestion'          => $areaOlimpiada->olimpiada->gestion ?? 'N/A'
                ];
            })
        ];
    }

    public function getAsignacionesActivas(int $userId): Collection
    {
        return EvaluadorAn::where('id_usuario', $userId)
            ->where('estado', 1)
            ->whereHas('areaNivel.areaOlimpiada.olimpiada', function($q) {
                $q->where('estado', 1);
            })
            ->with([
                'areaNivel.areaOlimpiada.area',
                'areaNivel.nivel'
            ])
            ->get();
    }
}
