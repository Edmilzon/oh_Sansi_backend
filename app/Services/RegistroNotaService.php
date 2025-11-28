<?php

namespace App\Services;

use App\Repositories\RegistroNotaRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class RegistroNotaService
{
    protected $registroNotaRepository;

    public function __construct(RegistroNotaRepository $registroNotaRepository)
    {
        $this->registroNotaRepository = $registroNotaRepository;
    }

    public function getHistorialCalificaciones(
        ?int $id_area = null,
        ?array $ids_niveles = null,
        int $page = 1,
        int $limit = 10
    ): array {
        try {
            $result = $this->registroNotaRepository->getHistorialCalificaciones(
                $id_area,
                $ids_niveles,
                $page,
                $limit
            );

            // Transformar los datos al formato requerido
            $data = $result->map(function ($registro) {
                return [
                    'id_historial' => $registro->id_registro_nota,
                    'fecha_hora' => $registro->fecha_hora,
                    'nombre_evaluador' => $registro->nombre_evaluador,
                    'nombre_olimpista' => $registro->nombre_olimpista,
                    'area' => $registro->area,
                    'nivel' => $registro->nivel,
                    'accion' => $this->normalizarAccion($registro->accion),
                    'observacion' => $registro->observacion,
                    'descripcion' => $registro->descripcion,
                    'id_area' => $registro->id_area,
                    'id_nivel' => $registro->id_nivel,
                    'nota_anterior' => $registro->nota_anterior,
                    'nota_nueva' => $registro->nota_nueva,
                ];
            });

            return [
                'success' => true,
                'data' => $data,
                'meta' => [
                    'total' => $result->total(),
                    'page' => $result->currentPage(),
                    'limit' => $result->perPage(),
                    'totalPages' => $result->lastPage(),
                ]
            ];

        } catch (\Exception $e) {
            Log::error('[SERVICE] Error al obtener historial de calificaciones:', [
                'id_area' => $id_area,
                'ids_niveles' => $ids_niveles,
                'page' => $page,
                'limit' => $limit,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'data' => [],
                'message' => 'Error al obtener el historial de calificaciones: ' . $e->getMessage(),
                'meta' => [
                    'total' => 0,
                    'page' => $page,
                    'limit' => $limit,
                    'totalPages' => 0,
                ]
            ];
        }
    }

    private function normalizarAccion(string $accion): string
    {
        $acciones = [
            'calificar' => 'Calificar',
            'modificar' => 'Modificar',
            'desclasificar' => 'Desclasificar',
            'corregir' => 'Modificar',
            'actualizar' => 'Modificar',
            // Agrega más mapeos según necesites
        ];

        $accionLower = strtolower($accion);
        
        return $acciones[$accionLower] ?? ucfirst($accion);
    }

    public function getAreasParaFiltro(): array
    {
        try {
            $areas = $this->registroNotaRepository->getAreasParaFiltro();

            return [
                'success' => true,
                'data' => $areas,
                'message' => 'Áreas obtenidas exitosamente para filtro'
            ];

        } catch (\Exception $e) {
            Log::error('[SERVICE] Error al obtener áreas para filtro:', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'data' => [],
                'message' => 'Error al obtener las áreas: ' . $e->getMessage()
            ];
        }
    }

    public function getNivelesPorArea(int $id_area): array
    {
        try {
            $niveles = $this->registroNotaRepository->getNivelesPorArea($id_area);

            return [
                'success' => true,
                'data' => $niveles,
                'message' => 'Niveles obtenidos exitosamente para el área'
            ];

        } catch (\Exception $e) {
            Log::error('[SERVICE] Error al obtener niveles por área:', [
                'id_area' => $id_area,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'data' => [],
                'message' => 'Error al obtener los niveles: ' . $e->getMessage()
            ];
        }
    }

    public function createRegistroNota(array $data): array
    {
        try {
            $registroNota = $this->registroNotaRepository->createRegistroNota($data);

            return [
                'success' => true,
                'data' => $registroNota,
                'message' => 'Registro de nota creado exitosamente'
            ];

        } catch (\Exception $e) {
            Log::error('[SERVICE] Error al crear registro de nota:', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Error al crear el registro de nota: ' . $e->getMessage()
            ];
        }
    }

    public function getRegistroNotaById(int $id): array
    {
        try {
            $registroNota = $this->registroNotaRepository->getById($id);

            if (!$registroNota) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Registro de nota no encontrado'
                ];
            }

            return [
                'success' => true,
                'data' => $registroNota,
                'message' => 'Registro de nota encontrado'
            ];

        } catch (\Exception $e) {
            Log::error('[SERVICE] Error al obtener registro de nota por ID:', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Error al obtener el registro de nota: ' . $e->getMessage()
            ];
        }
    }
}