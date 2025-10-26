<?php

namespace App\Services;

use App\Repositories\ParametroRepository;
use App\Repositories\AreaNivelRepository;
use Illuminate\Database\Eloquent\Collection;

class ParametroService
{
    protected $parametroRepository;
    protected $areaNivelRepository;

    public function __construct(
        ParametroRepository $parametroRepository,
        AreaNivelRepository $areaNivelRepository
    ) {
        $this->parametroRepository = $parametroRepository;
        $this->areaNivelRepository = $areaNivelRepository;
    }

    public function getAllParametros(): array
    {
        $parametros = $this->parametroRepository->getAll();

        $formatted = $parametros->map(function($parametro) {
            return $this->formatParametro($parametro);
        });

        return [
            'parametros' => $formatted,
            'total' => $parametros->count(),
            'message' => 'Parámetros obtenidos exitosamente'
        ];
    }

    public function getParametrosByOlimpiada(int $idOlimpiada): array
    {
        $parametros = $this->parametroRepository->getByOlimpiada($idOlimpiada);

        $formatted = $parametros->map(function($parametro) {
            return $this->formatParametro($parametro);
        });

        return [
            'parametros' => $formatted,
            'total' => $parametros->count(),
            'message' => "Parámetros obtenidos para la olimpiada {$idOlimpiada}"
        ];
    }

    public function createOrUpdateParametros(array $data): array
    {
        $results = [];
        $errors = [];

        foreach ($data['area_niveles'] as $areaNivelData) {
            try {
                $areaNivel = $this->areaNivelRepository->getById($areaNivelData['id_area_nivel']);
                
                if (!$areaNivel) {
                    $errors[] = "El área-nivel con ID {$areaNivelData['id_area_nivel']} no existe";
                    continue;
                }

                $parametro = $this->parametroRepository->updateOrCreateByAreaNivel(
                    $areaNivelData['id_area_nivel'],
                    [
                        'nota_max_clasif' => $areaNivelData['nota_max_clasif'],
                        'nota_min_clasif' => $areaNivelData['nota_min_clasif'],
                        'cantidad_max_apro' => $areaNivelData['cantidad_max_apro']
                    ]
                );

                $results[] = $this->formatParametro($parametro);

            } catch (\Exception $e) {
                $errors[] = "Error procesando área-nivel {$areaNivelData['id_area_nivel']}: " . $e->getMessage();
            }
        }

        $response = [
            'parametros_actualizados' => $results,
            'total_procesados' => count($results),
            'message' => count($results) . ' parámetros procesados exitosamente'
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
            $response['message'] .= ' con ' . count($errors) . ' errores';
        }

        return $response;
    }

    public function createOrUpdateParametro(array $data): array
    {
        $areaNivel = $this->areaNivelRepository->getById($data['id_area_nivel']);
        
        if (!$areaNivel) {
            throw new \Exception("El área-nivel con ID {$data['id_area_nivel']} no existe");
        }

        $parametro = $this->parametroRepository->updateOrCreateByAreaNivel(
            $data['id_area_nivel'],
            [
                'nota_max_clasif' => $data['nota_max_clasif'],
                'nota_min_clasif' => $data['nota_min_clasif'],
                'cantidad_max_apro' => $data['cantidad_max_apro']
            ]
        );

        return [
            'parametro' => $this->formatParametro($parametro),
            'message' => 'Parámetro guardado exitosamente'
        ];
    }

    private function formatParametro($parametro): array
    {
        return [
            'id_parametro' => $parametro->id_parametro,
            'nota_max_clasif' => $parametro->nota_max_clasif,
            'nota_min_clasif' => $parametro->nota_min_clasif,
            'cantidad_max_apro' => $parametro->cantidad_max_apro,
            'area_nivel' => [
                'id_area_nivel' => $parametro->areaNivel->id_area_nivel,
                'area' => [
                    'id_area' => $parametro->areaNivel->area->id_area,
                    'nombre' => $parametro->areaNivel->area->nombre
                ],
                'nivel' => [
                    'id_nivel' => $parametro->areaNivel->nivel->id_nivel,
                    'nombre' => $parametro->areaNivel->nivel->nombre
                ],
                'olimpiada' => [
                    'id_olimpiada' => $parametro->areaNivel->olimpiada->id_olimpiada,
                    'gestion' => $parametro->areaNivel->olimpiada->gestion,
                    'nombre' => $parametro->areaNivel->olimpiada->nombre
                ]
            ]
        ];
    }
}