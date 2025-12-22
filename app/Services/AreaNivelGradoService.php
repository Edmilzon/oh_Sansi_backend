<?php

namespace App\Services;

use App\Model\AreaNivel;
use App\Model\Olimpiada;
use App\Model\AreaOlimpiada;
use App\Model\Area;
use App\Model\Nivel;
use App\Model\GradoEscolaridad;
use App\Repositories\AreaNivelGradoRepository;
use App\Repositories\AreaNivelRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AreaNivelGradoService
{
    protected $areaNivelGradoRepository;
    protected $areaNivelRepository;

    public function __construct(
        AreaNivelGradoRepository $areaNivelGradoRepository,
        AreaNivelRepository $areaNivelRepository
    ) {
        $this->areaNivelGradoRepository = $areaNivelGradoRepository;
        $this->areaNivelRepository = $areaNivelRepository;
    }

    private function obtenerOlimpiadaActiva(): Olimpiada
    {
        $olimpiada = Olimpiada::where('estado', true)->first();
        
        if (!$olimpiada) {
            throw new \Exception('No hay ninguna olimpiada activa. Active una olimpiada primero.');
        }
        
        return $olimpiada;
    }


    public function createMultipleAreaNivelWithGrades(array $data): array
    {
        Log::info('[SERVICE] INICIANDO createMultipleAreaNivelWithGrades:', [
            'input_data' => $data,
            'input_count' => count($data),
        ]);

        if (!is_array($data) || empty($data)) {
            Log::warning('[SERVICE] Datos inválidos o vacíos recibidos');
            return [
                'area_niveles' => [],
                'olimpiada' => 'N/A',
                'message' => 'Error: Los datos no son un array válido o están vacíos',
                'errors' => ['Formato de datos inválido'],
                'success_count' => 0,
                'error_count' => 1,
            ];
        }

        DB::beginTransaction();
        try {
            $olimpiadaActiva = $this->obtenerOlimpiadaActiva();
            $inserted = [];
            $errors = [];

            $grupos = [];
            foreach ($data as $index => $relacion) {
                $clave = $relacion['id_area'] . '_' . $relacion['id_nivel'];
                
                if (!isset($grupos[$clave])) {
                    $grupos[$clave] = [
                        'id_area' => $relacion['id_area'],
                        'id_nivel' => $relacion['id_nivel'],
                        'es_activo' => $relacion['activo'],
                        'grados' => []
                    ];
                }
                
                $grupos[$clave]['grados'][] = $relacion['id_grado_escolaridad'];
            }

            foreach ($grupos as $clave => $grupo) {
                try {
                    $areaOlimpiada = AreaOlimpiada::firstOrCreate(
                        [
                            'id_area' => $grupo['id_area'],
                            'id_olimpiada' => $olimpiadaActiva->id_olimpiada
                        ]
                    );
                    
                    $areaNivel = AreaNivel::firstOrCreate(
                        [
                            'id_area_olimpiada' => $areaOlimpiada->id_area_olimpiada,
                            'id_nivel' => $grupo['id_nivel']
                        ],
                        [
                            'es_activo' => $grupo['es_activo']
                        ]
                    );

                    $areaNivel->gradosEscolaridad()->sync($grupo['grados']);

                    $inserted[] = $areaNivel->load(['areaOlimpiada.area', 'nivel', 'gradosEscolaridad']);

                } catch (\Exception $e) {
                    $errorMsg = "Error en grupo {$clave}: " . $e->getMessage();
                    $errors[] = $errorMsg;
                    Log::error("[SERVICE] {$errorMsg}");
                }
            }

            DB::commit();

            $message = '';
            if (count($inserted) > 0) {
                $message = "Se crearon/actualizaron " . count($inserted) . " relaciones área-nivel-grado correctamente en la olimpiada activa ({$olimpiadaActiva->gestion})";
            }
            
            if (count($errors) > 0) {
                $message .= ". Se encontraron " . count($errors) . " errores.";
            }

            return [
                'area_niveles' => $inserted,
                'olimpiada' => $olimpiadaActiva->gestion,
                'message' => $message,
                'errors' => $errors,
                'success_count' => count($inserted),
                'error_count' => count($errors)
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[SERVICE] Error general en createMultipleAreaNivelWithGrades:', [
                'exception' => $e->getMessage()
            ]);
            throw new \Exception("Error al procesar relaciones: " . $e->getMessage());
        }
    }

    public function index(): array
    {
        return $this->getAreasConNiveles();
    }

    public function getAreasConNiveles(): array
    {
        try {
            $olimpiadaActiva = $this->obtenerOlimpiadaActiva();
            
            $areas = Area::with([
                'areaOlimpiada' => function($query) use ($olimpiadaActiva) {
                    $query->where('id_olimpiada', $olimpiadaActiva->id_olimpiada);
                },
                'areaOlimpiada.areaNiveles' => function($query) {
                    $query->where('es_activo', true);
                },
                'areaOlimpiada.areaNiveles.nivel:id_nivel,nombre'
            ])
            ->orderBy('id_area', 'asc')
            ->get(['id_area', 'nombre']);

            $resultado = $areas->map(function($area) {
                $nivelesArray = collect();
                
                foreach ($area->areaOlimpiada as $areaOlimpiada) {
                    foreach ($areaOlimpiada->areaNiveles as $areaNivel) {
                        $nivelesArray->push([
                            'id_nivel' => $areaNivel->nivel->id_nivel,
                            'nombre' => $areaNivel->nivel->nombre,
                            'asignado_activo' => (bool) $areaNivel->es_activo
                        ]);
                    }
                }

                return [
                    'id_area' => $area->id_area,
                    'nombre' => $area->nombre,
                    'niveles' => $nivelesArray->unique('id_nivel')->values()->toArray()
                ];
            });

            return [
                'areas' => $resultado->values()->toArray(),
                'olimpiada_actual' => $olimpiadaActiva->gestion,
                'message' => 'Áreas obtenidas para la olimpiada activa'
            ];

        } catch (\Exception $e) {
            Log::error('[SERVICE] Error en getAreasConNiveles:', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'areas' => [],
                'olimpiada_actual' => 'N/A',
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    public function getAreasConNivelesSimplificado(): array
    {
        try {
            $olimpiadaActiva = $this->obtenerOlimpiadaActiva();
            
            $areas = Area::with([
                'areaOlimpiada' => function($query) use ($olimpiadaActiva) {
                    $query->where('id_olimpiada', $olimpiadaActiva->id_olimpiada);
                },
                'areaOlimpiada.areaNiveles' => function($query) {
                    $query->where('es_activo', true);
                },
                'areaOlimpiada.areaNiveles.nivel:id_nivel,nombre',
                'areaOlimpiada.areaNiveles.gradosEscolaridad:id_grado_escolaridad,nombre'
            ])
            ->whereHas('areaOlimpiada.areaNiveles', function($query) {
                $query->where('es_activo', true);
            })
            ->orderBy('id_area', 'asc')
            ->get(['id_area', 'nombre']);

            $resultado = $areas->map(function($area) {
                $nivelesAgrupados = collect();
                
                foreach ($area->areaOlimpiada as $areaOlimpiada) {
                    foreach ($areaOlimpiada->areaNiveles as $areaNivel) {
                        $nivelesAgrupados->push([
                            'id_nivel' => $areaNivel->nivel->id_nivel,
                            'nombre_nivel' => $areaNivel->nivel->nombre,
                            'grados' => $areaNivel->gradosEscolaridad->map(function($grado) {
                                return [
                                    'id_grado_escolaridad' => $grado->id_grado_escolaridad,
                                    'nombre_grado' => $grado->nombre
                                ];
                            })->values()->toArray()
                        ]);
                    }
                }

                return [
                    'id_area' => $area->id_area,
                    'nombre' => $area->nombre,
                    'niveles' => $nivelesAgrupados->unique('id_nivel')->values()->toArray()
                ];
            });

            return [
                'areas' => $resultado->values()->toArray(),
                'olimpiada_actual' => $olimpiadaActiva->gestion,
                'message' => 'Áreas con niveles y grados activos obtenidas exitosamente para la olimpiada activa'
            ];

        } catch (\Exception $e) {
            Log::error('[SERVICE] Error en getAreasConNivelesSimplificado:', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'areas' => [],
                'olimpiada_actual' => 'N/A',
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    public function getAreaNivelByAreaAll(int $id_area): array
    {
        try {
            $olimpiadaActiva = $this->obtenerOlimpiadaActiva();
            
            $areaNiveles = AreaNivel::whereHas('areaOlimpiada', function($query) use ($id_area, $olimpiadaActiva) {
                    $query->where('id_area', $id_area)
                          ->where('id_olimpiada', $olimpiadaActiva->id_olimpiada);
                })
                ->with(['nivel', 'gradosEscolaridad', 'areaOlimpiada.area'])
                ->get();

            return [
                'success' => true,
                'data' => $areaNiveles,
                'olimpiada' => $olimpiadaActiva->gestion,
                'message' => 'Relaciones área-nivel obtenidas para el área especificada en la olimpiada activa'
            ];

        } catch (\Exception $e) {
            Log::error('[SERVICE] Error en getAreaNivelByAreaAll:', [
                'id_area' => $id_area,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'data' => [],
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    public function getNivelesGradosByAreaAndGestion(int $id_area, string $gestion): array
    {
        try {
            Log::info('[SERVICE] Obteniendo niveles y grados por área y gestión', [
                'id_area' => $id_area,
                'gestion' => $gestion
            ]);

            $olimpiada = Olimpiada::where('gestion', $gestion)->first();

            if (!$olimpiada) {
                Log::warning('[SERVICE] Olimpiada no encontrada', ['gestion' => $gestion]);
                return [
                    'success' => false,
                    'data' => [
                        'niveles_con_grados_agrupados' => [],
                        'niveles_individuales' => []
                    ],
                    'message' => "No se encontró la olimpiada con gestión: {$gestion}"
                ];
            }

            $area = Area::find($id_area);
            if (!$area) {
                Log::warning('[SERVICE] Área no encontrada', ['id_area' => $id_area]);
                return [
                    'success' => false,
                    'data' => [
                        'niveles_con_grados_agrupados' => [],
                        'niveles_individuales' => []
                    ],
                    'message' => "No se encontró el área con ID: {$id_area}"
                ];
            }

            $areaOlimpiada = AreaOlimpiada::where('id_area', $id_area)
                ->where('id_olimpiada', $olimpiada->id_olimpiada)
                ->first();

            if (!$areaOlimpiada) {
                Log::warning('[SERVICE] Relación área-olimpiada no encontrada', [
                    'id_area' => $id_area,
                    'id_olimpiada' => $olimpiada->id_olimpiada
                ]);
                return [
                    'success' => false,
                    'data' => [
                        'niveles_con_grados_agrupados' => [],
                        'niveles_individuales' => []
                    ],
                    'message' => "El área no está asignada a la olimpiada de gestión {$gestion}"
                ];
            }

            $areaNiveles = AreaNivel::with([
                'nivel:id_nivel,nombre',
                'gradosEscolaridad:id_grado_escolaridad,nombre'
            ])
            ->where('id_area_olimpiada', $areaOlimpiada->id_area_olimpiada)
            ->where('es_activo', true)
            ->get();

            Log::info('[SERVICE] AreaNiveles encontrados', [
                'count' => $areaNiveles->count(),
                'ids' => $areaNiveles->pluck('id_area_nivel')->toArray()
            ]);

            $nivelesMap = [];
            
            foreach ($areaNiveles as $areaNivel) {
                $idNivel = $areaNivel->nivel->id_nivel;
                
                if (!isset($nivelesMap[$idNivel])) {
                    $nivelesMap[$idNivel] = [
                        'id_nivel' => $idNivel,
                        'nombre_nivel' => $areaNivel->nivel->nombre,
                        'grados' => []
                    ];
                }
                
                foreach ($areaNivel->gradosEscolaridad as $grado) {
                    $nivelesMap[$idNivel]['grados'][] = [
                        'id_grado_escolaridad' => $grado->id_grado_escolaridad,
                        'nombre' => $grado->nombre
                    ];
                }
            }

            foreach ($nivelesMap as &$nivelData) {
                $nivelData['grados'] = collect($nivelData['grados'])
                    ->unique('id_grado_escolaridad')
                    ->values()
                    ->toArray();
            }

            $nivelesIndividuales = $areaNiveles->map(function($areaNivel) {
                return [
                    'id_area_nivel' => $areaNivel->id_area_nivel,
                    'nivel' => [
                        'id_nivel' => $areaNivel->nivel->id_nivel,
                        'nombre' => $areaNivel->nivel->nombre
                    ]
                ];
            })->values();

            $response = [
                'success' => true,
                'data' => [
                    'niveles_con_grados_agrupados' => array_values($nivelesMap),
                    'niveles_individuales' => $nivelesIndividuales->toArray()
                ]
            ];

            Log::info('[SERVICE] Respuesta formateada', [
                'niveles_agrupados_count' => count($nivelesMap),
                'niveles_individuales_count' => $nivelesIndividuales->count()
            ]);

            return $response;

        } catch (\Exception $e) {
            Log::error('[SERVICE] Error al obtener niveles y grados por área y gestión:', [
                'id_area' => $id_area,
                'gestion' => $gestion,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'data' => [
                    'niveles_con_grados_agrupados' => [],
                    'niveles_individuales' => []
                ],
                'message' => 'Error al obtener los niveles y grados: ' . $e->getMessage()
            ];
        }
    }

    public function getNivelesGradosByAreasAndGestion(array $idAreas, string $gestion): array
    {
        try {
            $olimpiada = Olimpiada::where('gestion', $gestion)->first();

            if (!$olimpiada) {
                return [
                    'success' => false,
                    'data' => [],
                    'message' => "No se encontró la olimpiada con gestión: {$gestion}"
                ];
            }

            $areaNiveles = AreaNivel::whereHas('areaOlimpiada', function($query) use ($idAreas, $olimpiada) {
                    $query->whereIn('id_area', $idAreas)
                          ->where('id_olimpiada', $olimpiada->id_olimpiada);
                })
                ->with(['areaOlimpiada.area', 'nivel', 'gradosEscolaridad'])
                ->get();

            return [
                'success' => true,
                'data' => $areaNiveles,
                'olimpiada' => $olimpiada->gestion,
                'message' => "Relaciones área-nivel obtenidas para la gestión {$gestion}"
            ];

        } catch (\Exception $e) {
            Log::error('[SERVICE] Error en getNivelesGradosByAreasAndGestion:', [
                'id_areas' => $idAreas,
                'gestion' => $gestion,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'data' => [],
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    public function getByGestionAndAreas(string $gestion, array $idAreas): array
    {
        try {
            $olimpiada = Olimpiada::where('gestion', $gestion)->first();
            
            if (!$olimpiada) {
                throw new \Exception("No se encontró la olimpiada con gestión: {$gestion}");
            }
            
            $areaNiveles = AreaNivel::whereHas('areaOlimpiada', function($query) use ($idAreas, $olimpiada) {
                    $query->whereIn('id_area', $idAreas)
                          ->where('id_olimpiada', $olimpiada->id_olimpiada);
                })
                ->with(['areaOlimpiada.area', 'nivel', 'gradosEscolaridad'])
                ->get();

            return [
                'area_niveles' => $areaNiveles,
                'olimpiada' => $olimpiada->gestion,
                'message' => "Relaciones área-nivel obtenidas para la gestión {$gestion}"
            ];
        } catch (\Exception $e) {
            Log::error('[SERVICE] Error en getByGestionAndAreas:', [
                'gestion' => $gestion,
                'id_areas' => $idAreas,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function verificarAccesoResponsable(int $idOlimpiada, int $idArea, int $idResponsable): bool
    {
        return DB::table('area_olimpiada as ao')
            ->join('responsable_area as ra', 'ao.id_area_olimpiada', '=', 'ra.id_area_olimpiada')
            ->where('ao.id_olimpiada', $idOlimpiada)
            ->where('ao.id_area', $idArea)
            ->where('ra.id_usuario', $idResponsable)
            ->exists();
    }

    public function getAreasPorResponsable(int $idResponsable): array
    {
        try {
            $olimpiadaActiva = DB::table('olimpiada')
                ->where('estado', true)
                ->first();
            
            if (!$olimpiadaActiva) {
                return [];
            }
            
            $areas = DB::table('area_olimpiada as ao')
                ->join('responsable_area as ra', 'ao.id_area_olimpiada', '=', 'ra.id_area_olimpiada')
                ->join('area as a', 'ao.id_area', '=', 'a.id_area')
                ->where('ao.id_olimpiada', $olimpiadaActiva->id_olimpiada)
                ->where('ra.id_usuario', $idResponsable)
                ->select('a.id_area', 'a.nombre')
                ->distinct()
                ->get()
                ->toArray();
            
            return $areas;
            
        } catch (\Exception $e) {
            Log::error('Error al obtener áreas por responsable:', [
                'idResponsable' => $idResponsable,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}