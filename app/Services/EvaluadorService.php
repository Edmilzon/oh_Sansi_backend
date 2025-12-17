<?php

namespace App\Services;

use App\Repositories\EvaluadorRepository;
use App\Repositories\UsuarioRepository;
use App\Model\Usuario;
use App\Mail\UserCredentialsMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class EvaluadorService
{
    public function __construct(
        protected EvaluadorRepository $repo,
        protected UsuarioRepository $usuarioRepo
    ) {}

    public function obtenerDashboard(int $userId): array
    {
        $examenes = $this->repo->obtenerExamenesActivosPorJuez($userId);

        return $examenes->map(function ($examen) {

            $nombreCompetencia = $examen->competencia->nombre ?? 'Olimpiada';
            $nombreArea = $examen->competencia->areaNivel->areaOlimpiada->area->nombre ?? 'General';
            $nombreNivel = $examen->competencia->areaNivel->nivel->nombre ?? 'Todos';

            $horaInicio = $examen->fecha_inicio_real
                ? Carbon::parse($examen->fecha_inicio_real)->format('H:i')
                : '--:--';

            return [
                'id_examen' => $examen->id_examen,
                'titulo_examen' => $examen->nombre,
                'descripcion_breve' => "Nota Máx: {$examen->maxima_nota} - Ponderación: {$examen->ponderacion}%",
                'estado_visual' => 'EN_CURSO',
                'color_estado' => 'green',
                'contexto' => [
                    'competencia' => $nombreCompetencia,
                    'area' => $nombreArea,
                    'nivel' => $nombreNivel,
                ],
                'acciones' => [
                    'puede_ingresar' => true,
                    'link_sala' => "/sala-evaluacion/{$examen->id_examen}",
                    'texto_boton' => 'Ingresar a Sala'
                ],
                'inicio_real' => $horaInicio,
                'tiempo_transcurrido' => $examen->fecha_inicio_real
                    ? Carbon::parse($examen->fecha_inicio_real)->diffForHumans(['parts' => 1])
                    : 'Recién iniciado'
            ];
        })->values()->toArray();
    }

    public function createEvaluador(array $data): array
    {
        return DB::transaction(function () use ($data) {

            $persona = $this->repo->findOrCreatePersona($data);

            $usuario = $this->repo->createUsuario($persona, $data);

            $this->repo->assignEvaluadorRole($usuario, $data['id_olimpiada']);

            $this->repo->syncEvaluadorAreas($usuario, $data['area_nivel_ids']);

            $this->sendCredentialsEmail($usuario, $data['password']);

            return $this->repo->getById($usuario->id_usuario);
        });
    }

    public function addAsignacionesToEvaluador(string $ci, int $idOlimpiada, array $areaNivelIds): array
    {
        return DB::transaction(function () use ($ci, $idOlimpiada, $areaNivelIds) {

            $usuario = Usuario::whereHas('persona', function ($query) use ($ci) {
                $query->where('ci', $ci);
            })->first();

            if (!$usuario) {
                throw new Exception("No se encontró ningún usuario con el CI: {$ci}");
            }

            $this->repo->assignEvaluadorRole($usuario, $idOlimpiada);

            $this->repo->syncEvaluadorAreas($usuario, $areaNivelIds);

            return [
                'id_usuario' => $usuario->id_usuario,
                'nombre'     => $usuario->persona->nombre . ' ' . $usuario->persona->apellido,
                'nuevas_asignaciones' => count($areaNivelIds),
                'mensaje'    => 'Asignaciones actualizadas correctamente.'
            ];
        });
    }

    public function getEvaluadorById(int $id): ?array
    {
        return $this->repo->getById($id);
    }

    private function sendCredentialsEmail(Usuario $usuario, string $rawPassword): void
    {
        try {
            if (!empty($usuario->email)) {
                Mail::to($usuario->email)->queue(
                    new UserCredentialsMail(
                        $usuario->persona->nombre,
                        $usuario->email,
                        $rawPassword,
                        'Evaluador'
                    )
                );
            }
        } catch (\Throwable $e) {
            Log::error("Error enviando correo de bienvenida al usuario {$usuario->id_usuario}: " . $e->getMessage());
        }
    }

    public function obtenerAreasNivelesAgrupados(int $userId): array
    {
        $esEvaluador = $this->usuarioRepo->tieneRol($userId, 'Evaluador');

        if (!$esEvaluador) {
            throw new Exception("El usuario no tiene el rol de 'Evaluador'.", 403);
        }

        $asignaciones = $this->repo->getAsignacionesActivas($userId);

        if ($asignaciones->isEmpty()) {
            return ['areas' => []];
        }

        $areasAgrupadas = $asignaciones->groupBy(function ($item) {
            return $item->areaNivel->areaOlimpiada->area->id_area;
        });

        $resultado = [];

        foreach ($areasAgrupadas as $idArea => $items) {
            $primero = $items->first();
            $nombreArea = $primero->areaNivel->areaOlimpiada->area->nombre;

            $niveles = $items->map(function ($item) {
                return [
                    'id_area_nivel' => $item->id_area_nivel,
                    'id_nivel'      => $item->areaNivel->nivel->id_nivel,
                    'nombre'        => $item->areaNivel->nivel->nombre,
                ];
            })->values()->toArray();

            $resultado[] = [
                'id_area' => (string) $idArea,
                'area'    => $nombreArea,
                'niveles' => $niveles
            ];
        }

        return ['areas' => $resultado];
    }
}
