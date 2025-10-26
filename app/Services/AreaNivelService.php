<?php

namespace App\Services;

use App\Model\AreaNivel;
use App\Model\Olimpiada;
use App\Model\AreaOlimpiada;
use App\Model\Area;
use App\Model\Nivel;
use App\Repositories\AreaNivelRepository;
use Illuminate\Database\Eloquent\Collection;

class AreaNivelService
{
    protected $areaNivelRepository;

    public function __construct(AreaNivelRepository $areaNivelRepository)
    {
        $this->areaNivelRepository = $areaNivelRepository;
    }

    private function obtenerOlimpiadaActual(): Olimpiada
    {
        $gestionActual = date('Y');
        $nombreOlimpiada = "Olimpiada Científica Estudiantil $gestionActual";
        
        return Olimpiada::firstOrCreate(
            ['gestion' => "$gestionActual"],
            ['nombre' => $nombreOlimpiada]
        );
    }

    public function getAreaNivelList(): Collection
    {
        return $this->areaNivelRepository->getAllAreasNiveles();
    }

    public function getAreaNivelByArea(int $id_area): Collection
    {
        $olimpiadaActual = $this->obtenerOlimpiadaActual();
        return $this->areaNivelRepository->getByArea($id_area, $olimpiadaActual->id_olimpiada);
    }

    public function getAreaNivelById(int $id): ?array
    {
        $areaNivel = $this->areaNivelRepository->getById($id);
        
        if (!$areaNivel) {
            return null;
        }

        return [
            'area_nivel' => $areaNivel,
            'message' => 'Relación área-nivel encontrada'
        ];
    }

    public function getAreaNivelByAreaAll(int $id_area): Collection
    {
        $olimpiadaActual = $this->obtenerOlimpiadaActual();
        return $this->areaNivelRepository->getByAreaAll($id_area, $olimpiadaActual->id_olimpiada);
    }

    public function getAreaNivelesAsignadosAll(): array
    {
        $olimpiadaActual = $this->obtenerOlimpiadaActual();
        $areas = $this->areaNivelRepository->getAreaNivelAsignadosAll($olimpiadaActual->id_olimpiada);
    
        $resultado = $areas->filter(function($area) {
            if ($area->areaNiveles->isEmpty()) {
                return true;
            }
            
            return $area->areaNiveles->contains('activo', true);
        })->map(function($area) {
            
            if ($area->areaNiveles->isEmpty()) {
                return [
                    'id_area' => $area->id_area,
                    'nombre' => $area->nombre,
                    'niveles' => []
                ];
            }
            
            $nivelesArray = $area->areaNiveles->filter(function($areaNivel) {
                return $areaNivel->activo === true;
            })->map(function($areaNivel) {
                return [
                    'id_nivel' => $areaNivel->nivel->id_nivel,
                    'nombre' => $areaNivel->nivel->nombre,
                    'asignado_activo' => $areaNivel->activo
                ];
            });
            
            return [
                'id_area' => $area->id_area,
                'nombre' => $area->nombre,
                'niveles' => $nivelesArray->values()
            ];
        });
    
        return [
            'areas' => $resultado->values(),
            'olimpiada_actual' => $olimpiadaActual->gestion,
            'message' => 'Se muestran las áreas que tienen al menos una relación activa o no tienen relaciones'
        ];
    }

    public function createNewAreaNivel(array $data): array
    {
        $olimpiadaActual = $this->obtenerOlimpiadaActual();
        
        $this->areaNivelRepository->createOrGetAreaOlimpiada(
            $data['id_area'], 
            $olimpiadaActual->id_olimpiada
        );

        $existing = $this->areaNivelRepository->getByAreaAndNivel(
            $data['id_area'], 
            $data['id_nivel'], 
            $olimpiadaActual->id_olimpiada
        );

        if ($existing) {
            throw new \Exception('Ya existe este nivel asociado a esta área en la olimpiada actual.');
        }

        $data['id_olimpiada'] = $olimpiadaActual->id_olimpiada;
        $areaNivel = $this->areaNivelRepository->createAreaNivel($data);

        return [
            'area_nivel' => $areaNivel,
            'message' => 'Relación área-nivel creada exitosamente'
        ];
    }

    public function createMultipleAreaNivel(array $data): array
    {
        \Log::info('[SERVICE] INICIANDO createMultipleAreaNivel:', [
            'input_data' => $data,
            'input_count' => count($data),
            'input_type' => gettype($data),
            'is_array' => is_array($data),
            'first_element' => $data[0] ?? 'no first element'
        ]);

        if (!is_array($data)) {
            \Log::error('[SERVICE] NO ES ARRAY:', ['type' => gettype($data)]);
            return [
                'area_niveles' => [],
                'olimpiada' => 'N/A',
                'message' => 'Error: Los datos no son un array válido',
                'errors' => ['Formato de datos inválido'],
                'success_count' => 0,
                'error_count' => 1,
                'total_relations' => 0,
                'processed_count' => 0
            ];
        }

        if (empty($data)) {
            \Log::warning('[SERVICE] Array VACÍO recibido en Service');
            return [
                'area_niveles' => [],
                'olimpiada' => 'N/A',
                'message' => 'No se recibieron relaciones para procesar - array vacío',
                'errors' => ['El array de relaciones recibido está vacío'],
                'success_count' => 0,
                'error_count' => 1,
                'total_relations' => 0,
                'processed_count' => 0
            ];
        }

        try {
            $olimpiadaActual = $this->obtenerOlimpiadaActual();
            \Log::info('[SERVICE] Olimpiada actual obtenida:', [
                'id_olimpiada' => $olimpiadaActual->id_olimpiada,
                'gestion' => $olimpiadaActual->gestion,
                'nombre' => $olimpiadaActual->nombre
            ]);
            
            $inserted = [];
            $errors = [];
            $totalRelations = count($data);
            
            \Log::info("[SERVICE] Procesando {$totalRelations} relaciones");

            foreach ($data as $index => $relacion) {
                \Log::info("[SERVICE] Procesando relación {$index}:", $relacion);
                
                try {
                    $area = Area::find($relacion['id_area']);
                    \Log::info("[SERVICE] Búsqueda de área {$relacion['id_area']}:", [
                        'existe' => !is_null($area),
                        'area_data' => $area ? $area->toArray() : 'NO EXISTE'
                    ]);
                    
                    if (!$area) {
                        $errorMsg = "Relación {$index}: El área {$relacion['id_area']} no existe";
                        $errors[] = $errorMsg;
                        \Log::warning("[SERVICE] {$errorMsg}");
                        continue;
                    }

                    $nivel = Nivel::find($relacion['id_nivel']);
                    \Log::info("[SERVICE] Búsqueda de nivel {$relacion['id_nivel']}:", [
                        'existe' => !is_null($nivel),
                        'nivel_data' => $nivel ? $nivel->toArray() : 'NO EXISTE'
                    ]);
                    
                    if (!$nivel) {
                        $errorMsg = "Relación {$index}: El nivel {$relacion['id_nivel']} no existe";
                        $errors[] = $errorMsg;
                        \Log::warning("[SERVICE] {$errorMsg}");
                        continue;
                    }

                    $areaOlimpiada = AreaOlimpiada::where('id_area', $relacion['id_area'])
                        ->where('id_olimpiada', $olimpiadaActual->id_olimpiada)
                        ->first();
                        
                    \Log::info("[SERVICE] Verificación area_olimpiada:", [
                        'id_area' => $relacion['id_area'],
                        'id_olimpiada' => $olimpiadaActual->id_olimpiada,
                        'existe' => !is_null($areaOlimpiada),
                        'area_olimpiada_data' => $areaOlimpiada ? $areaOlimpiada->toArray() : 'NO ASOCIADA'
                    ]);

                    if (!$areaOlimpiada) {
                        $errorMsg = "Relación {$index}: El área {$relacion['id_area']} no está asociada a la olimpiada actual ({$olimpiadaActual->gestion})";
                        $errors[] = $errorMsg;
                        \Log::warning("[SERVICE] {$errorMsg}");
                        continue;
                    }

                    $existing = $this->areaNivelRepository->getByAreaAndNivel(
                        $relacion['id_area'],
                        $relacion['id_nivel'],
                        $olimpiadaActual->id_olimpiada
                    );

                    \Log::info("[SERVICE] Verificación relación área-nivel existente:", [
                        'existe' => !is_null($existing),
                        'existing_data' => $existing ? $existing->toArray() : 'NO EXISTE'
                    ]);

                    if ($existing) {
                        $errorMsg = "Relación {$index}: El nivel {$relacion['id_nivel']} ya está asignado al área {$relacion['id_area']} en la gestión actual";
                        $errors[] = $errorMsg;
                        \Log::warning("[SERVICE] {$errorMsg}");
                        continue;
                    }

                    $areaNivel = $this->areaNivelRepository->createAreaNivel([
                        'id_area' => $relacion['id_area'],
                        'id_nivel' => $relacion['id_nivel'],
                        'id_olimpiada' => $olimpiadaActual->id_olimpiada,
                        'activo' => $relacion['activo']
                    ]);
                    
                    $inserted[] = $areaNivel;
                    \Log::info("[SERVICE] Relación {$index} creada exitosamente:", [
                        'id_area_nivel' => $areaNivel->id,
                        'data' => $areaNivel->toArray()
                    ]);

                } catch (\Exception $e) {
                    $errorMsg = "Relación {$index}: Error inesperado - " . $e->getMessage();
                    $errors[] = $errorMsg;
                    \Log::error("[SERVICE] {$errorMsg}", [
                        'exception' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            $processedCount = count($inserted) + count($errors);
            \Log::info("[SERVICE] Resumen procesamiento:", [
                'total_relaciones' => $totalRelations,
                'procesadas' => $processedCount,
                'éxitos' => count($inserted),
                'errores' => count($errors),
                'olimpiada' => $olimpiadaActual->gestion
            ]);

            $message = '';
            
            if (count($inserted) > 0) {
                $message = "✅ " . count($inserted) . " de {$totalRelations} relaciones creadas exitosamente para {$olimpiadaActual->gestion}";
            }
            
            if (count($errors) > 0) {
                if (count($inserted) > 0) {
                    $message .= ". ⚠️ " . count($errors) . " relaciones con errores";
                } else {
                    $message = "❌ Ninguna de las {$totalRelations} relaciones pudo ser procesada. Motivos:";
                }
            }

            if ($processedCount === 0 && $totalRelations > 0) {
                $message = "❌ Error crítico: {$totalRelations} relaciones recibidas pero ninguna fue procesada - revisar logs";
            }

            $result = [
                'area_niveles' => $inserted,
                'olimpiada' => $olimpiadaActual->gestion,
                'message' => $message,
                'errors' => $errors,
                'success_count' => count($inserted),
                'error_count' => count($errors),
                'total_relations' => $totalRelations,
                'processed_count' => $processedCount
            ];

            \Log::info('[SERVICE] Resultado final:', $result);
            return $result;

        } catch (\Exception $e) {
            \Log::error('[SERVICE] Error general en createMultipleAreaNivel:', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input_data' => $data
            ]);
            throw new \Exception("Error al procesar relaciones: " . $e->getMessage());
        }
    }

    public function updateAreaNivelByArea(int $id_area, array $niveles): array
    {
        $olimpiadaActual = $this->obtenerOlimpiadaActual();
        $updatedNiveles = [];
    
        foreach ($niveles as $nivelData) {
            $this->areaNivelRepository->createOrGetAreaOlimpiada(
                $id_area,
                $olimpiadaActual->id_olimpiada
            );

            $areaNivel = $this->areaNivelRepository->getByAreaAndNivel(
                $id_area,
                $nivelData['id_nivel'],
                $olimpiadaActual->id_olimpiada
            );

            if ($areaNivel) {
                $areaNivel->update(['activo' => $nivelData['activo']]);
                $updatedNiveles[] = $areaNivel;
            } else {
                $newAreaNivel = $this->areaNivelRepository->createAreaNivel([
                    'id_area' => $id_area,
                    'id_nivel' => $nivelData['id_nivel'],
                    'id_olimpiada' => $olimpiadaActual->id_olimpiada,
                    'activo' => $nivelData['activo']
                ]);
                $updatedNiveles[] = $newAreaNivel;
            }
        }

        return [
            'area_niveles' => $updatedNiveles,
            'olimpiada' => $olimpiadaActual->gestion,
            'message' => 'Relaciones área-nivel actualizadas exitosamente para la gestión actual'
        ];
    }

    public function updateAreaNivel(int $id, array $data): array
    {
        $updated = $this->areaNivelRepository->update($id, $data);

        if (!$updated) {
            throw new \Exception('Relación área-nivel no encontrada');
        }

        $areaNivel = $this->areaNivelRepository->getById($id);

        return [
            'area_nivel' => $areaNivel,
            'message' => 'Relación área-nivel actualizada exitosamente'
        ];
    }

    public function deleteAreaNivel(int $id): array
    {
        $deleted = $this->areaNivelRepository->delete($id);

        if (!$deleted) {
            throw new \Exception('Relación área-nivel no encontrada');
        }

        return [
            'message' => 'Relación área-nivel eliminada exitosamente'
        ];
    }

    public function getAreasConNivelesSimplificado(): array
    {
    $olimpiadaActual = $this->obtenerOlimpiadaActual();
    $areas = $this->areaNivelRepository->getAreasConNivelesSimplificado($olimpiadaActual->id_olimpiada);

    $resultado = $areas->map(function($area) {
        $niveles = $area->areaNiveles->map(function($areaNivel) {
            return [
                'id_nivel' => $areaNivel->nivel->id_nivel,
                'nombre' => $areaNivel->nivel->nombre
            ];
        });

        return [
            'id_area' => $area->id_area,
            'nombre' => $area->nombre,
            'niveles' => $niveles->values()
        ];
    });

    return [
        'areas' => $resultado->values(),
        'olimpiada_actual' => $olimpiadaActual->gestion,
        'message' => 'Áreas con niveles activos obtenidas exitosamente'
    ];
    }

    public function getAreasConNivelesPorOlimpiada(int $idOlimpiada): array
    {
    $olimpiada = Olimpiada::findOrFail($idOlimpiada);
    $areas = $this->areaNivelRepository->getAreasConNivelesSimplificado($idOlimpiada);

    $resultado = $areas->map(function($area) {
        $niveles = $area->areaNiveles->map(function($areaNivel) {
            return [
                'id_nivel' => $areaNivel->nivel->id_nivel,
                'nombre' => $areaNivel->nivel->nombre
            ];
        });

        return [
            'id_area' => $area->id_area,
            'nombre' => $area->nombre,
            'niveles' => $niveles->values()
        ];
    });

    return [
        'areas' => $resultado->values(),
        'olimpiada' => $olimpiada->gestion,
        'message' => "Áreas con niveles activos obtenidas para la gestión {$olimpiada->gestion}"
    ];
    }

    public function getAreasConNivelesPorGestion(string $gestion): array
    {
    $olimpiada = Olimpiada::where('gestion', $gestion)->firstOrFail();
    return $this->getAreasConNivelesPorOlimpiada($olimpiada->id_olimpiada);
    }


}