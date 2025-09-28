<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportarRequest;
use App\Repositories\CompetidorRepository;
use App\Services\CompetidorService;
use App\Models\Institucion;
use App\Models\Grupo;
use App\Models\GrupoCompetidor;
use App\Models\Area;
use App\Models\Nivel;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ImportarcsvController extends Controller
{
    protected $competidorService;
    protected $competidorRepository;

    public function __construct(
        CompetidorService $competidorService, 
        CompetidorRepository $competidorRepository
    ) {
        $this->competidorService = $competidorService;
        $this->competidorRepository = $competidorRepository;
    }

    public function importar(ImportarRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $competidoresCreados = [];

            foreach ($request->input('competidores') as $competidorData) {
                
                // 1. Buscar o crear Institución
                $institucion = Institucion::firstOrCreate(
                    ['nombre' => $competidorData['institucion']['nombre']],
                    [
                        'tipo' => $competidorData['institucion']['tipo'] ?? null,
                        'departamento' => $competidorData['institucion']['departamento'] ?? null,
                        'direccion' => $competidorData['institucion']['direccion'] ?? null,
                        'telefono' => $competidorData['institucion']['telefono'] ?? null,
                    ]
                );

                // 2. Buscar o crear Grupo
                $grupo = null;
                if (isset($competidorData['grupo']['nombre']) && !empty($competidorData['grupo']['nombre'])) {
                    $grupo = Grupo::firstOrCreate(
                        ['nombre' => $competidorData['grupo']['nombre']],
                        [
                            'descripcion' => $competidorData['grupo']['descripcion'] ?? null,
                            'max_integrantes' => $competidorData['grupo']['max_integrantes'] ?? null,
                        ]
                    );
                }

                // 3. Buscar o crear Área
                $area = Area::firstOrCreate(
                    ['nombre' => $competidorData['area']['nombre']],
                    ['descripcion' => $competidorData['area']['descripcion'] ?? null]
                );

                // 4. Buscar o crear Nivel
                $nivel = Nivel::firstOrCreate(
                    ['nombre' => $competidorData['nivel']['nombre']],
                    ['descripcion' => $competidorData['nivel']['descripcion'] ?? null]
                );

                // 5. Preparar datos para el Service
                $data = [
                    // Datos de Persona
                    'nombre' => $competidorData['persona']['nombre'],
                    'apellido' => $competidorData['persona']['apellido'],
                    'ci' => $competidorData['persona']['ci'],
                    'fecha_nac' => $competidorData['persona']['fecha_nac'] ?? null,
                    'genero' => $competidorData['persona']['genero'] ?? null,
                    'telefono' => $competidorData['persona']['telefono'] ?? null,
                    'email' => $competidorData['persona']['email'],

                    // Datos de Competidor
                    'grado_escolar' => $competidorData['competidor']['grado_escolar'] ?? null,
                    'departamento' => $competidorData['competidor']['departamento'] ?? null,
                    'contacto_tutor' => $competidorData['competidor']['contacto_tutor'] ?? null,
                    'contacto_emergencia' => $competidorData['competidor']['contacto_emergencia'] ?? null,
                    
                    // IDs relacionales
                    'id_institucion' => $institucion->id_institucion,
                    'id_area' => $area->id_area,
                    'id_nivel' => $nivel->id_nivel,
                ];

                // 6. Crear competidor
                $persona = $this->competidorService->createNewCompetidor($data);

                // 7. Asignar al grupo si existe
                if ($grupo) {
                    GrupoCompetidor::create([
                        'id_grupo' => $grupo->id_grupo,
                        'id_competidor' => $persona->competidor->id_competidor,
                    ]);
                }

                $competidoresCreados[] = $persona;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Competidores importados exitosamente',
                'data' => [
                    'total_importados' => count($competidoresCreados),
                    'competidores' => $competidoresCreados
                ]
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al importar competidores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todos los competidores
     */
    public function index(): JsonResponse
    {
        try {
            $competidores = $this->competidorRepository->getAllCompetidores();

            return response()->json([
                'success' => true,
                'data' => $competidores
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener competidores',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}