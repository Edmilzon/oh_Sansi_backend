<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportarRequest;
use App\Repositories\CompetidorRepository;
use App\Services\CompetidorService;
use App\Models\Institucion;
use App\Models\Grupo;
use App\Models\GrupoCompetidor;
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

            // 1. Buscar o crear Institución
            $institucion = Institucion::firstOrCreate(
                ['nombre' => $request->input('institucion.nombre')],
                [
                    'tipo' => $request->input('institucion.tipo'),
                    'departamento' => $request->input('institucion.departamento'),
                    'direccion' => $request->input('institucion.direccion'),
                    'telefono' => $request->input('institucion.telefono'),
                ]
            );

            // 4. Preparar datos para el Service
            $competidorData = [
                // Datos de Persona
                'nombre' => $request->input('persona.nombre'),
                'apellido' => $request->input('persona.apellido'),
                'ci' => $request->input('persona.ci'),
                'fecha_nac' => $request->input('persona.fecha_nac'),
                'genero' => $request->input('persona.genero'),
                'telefono' => $request->input('persona.telefono'),
                'email' => $request->input('persona.email'),

                // Datos de Competidor
                'grado_escolar' => $request->input('competidor.grado_escolar'),
                'departamento' => $request->input('competidor.departamento'),
                'contacto_tutor' => $request->input('competidor.contacto_tutor'),
                'contacto_emergencia' => $request->input('competidor.contacto_emergencia'),
                
                // IDs relacionales
                'id_institucion' => $institucion->id_institucion,
            ];

            // 3. Crear competidor usando el Service
            $persona = $this->competidorService->createNewCompetidor($competidorData);

            // 4. Manejar grupo si se proporciona
            if ($request->filled('grupo.nombre')) {
                $grupo = Grupo::firstOrCreate(
                    ['nombre' => $request->input('grupo.nombre')],
                    [
                        'descripcion' => $request->input('grupo.descripcion'),
                        'max_integrantes' => $request->input('max_integrantes'),
                    ]
                );

                GrupoCompetidor::create([
                    'id_grupo' => $grupo->id_grupo,
                    'id_competidor' => $persona->competidor->id_competidor,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Competidor importado exitosamente',
                'data' => $persona
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
                'message' => 'Error al importar competidor',
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