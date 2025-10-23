<?php

<<<<<<< HEAD
namespace app\Http\Controllers;

use App\Models\Persona;
use App\Models\Institucion;
use App\Models\Competidor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class ImportarcsvController extends Controller
{
    public function importar(Request $request): JsonResponse
    {
        try {
            // Validación básica del archivo
            if (!$request->hasFile('archivo_csv')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se envió ningún archivo'
                ], 400);
            }

            $archivo = $request->file('archivo_csv');
            $rutaArchivo = $archivo->getPathname();

            // Leer CSV
            $csv = Reader::createFromPath($rutaArchivo, 'r');
            $csv->setHeaderOffset(0);
            
            $registros = $csv->getRecords();
            $resultados = [];
            $contador = 0;

            foreach ($registros as $fila) {
                $contador++;
                
                // Insertar en base de datos
                $resultadoFila = $this->insertarFila($fila, $contador);
                $resultados[] = $resultadoFila;
            }

            return response()->json([
                'success' => true,
                'message' => 'Importación completada',
                'total_filas' => $contador,
                'resultados' => $resultados
=======
namespace App\Http\Controllers;

use App\Http\Requests\ImportarRequest;
use App\Repositories\CompetidorRepository;
use App\Services\CompetidorService;
use App\Models\Institucion;
use App\Models\Grupo;
use App\Models\GrupoCompetidor;
use App\Models\Area;
use App\Models\Nivel;
use App\Models\Competidor;
use App\Models\Persona;
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
            $competidoresDuplicados = [];
            $competidoresConError = [];

            foreach ($request->input('competidores') as $index => $competidorData) {
                
                try {
                    // 1. Validar que el area exista
                    $area = Area::where('nombre', $competidorData['area']['nombre'])->first();
                    if (!$area) {
                        $competidoresConError[] = [
                            'indice' => $index,
                            'nombre' => $competidorData['persona']['nombre'] . ' ' . $competidorData['persona']['apellido'],
                            'error' => "El área '{$competidorData['area']['nombre']}' no existe en la base de datos"
                        ];
                        continue;
                    }

                    // 2. Validar que el nivel exista
                    $nivel = Nivel::where('nombre', $competidorData['nivel']['nombre'])->first();
                    if (!$nivel) {
                        $competidoresConError[] = [
                            'indice' => $index,
                            'nombre' => $competidorData['persona']['nombre'] . ' ' . $competidorData['persona']['apellido'],
                            'error' => "El nivel '{$competidorData['nivel']['nombre']}' no existe en la base de datos"
                        ];
                        continue;
                    }

                    // 3. Buscar o crear Institución
                    $institucion = Institucion::firstOrCreate(
                        ['nombre' => $competidorData['institucion']['nombre']],
                        [
                            'tipo' => $competidorData['institucion']['tipo'] ?? null,
                            'departamento' => $competidorData['institucion']['departamento'] ?? null,
                            'direccion' => $competidorData['institucion']['direccion'] ?? null,
                            'telefono' => $competidorData['institucion']['telefono'] ?? null,
                        ]
                    );

                    // 4. Buscar o crear Grupo
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

                    // 5. Verificar duplicados por área y nivel
                    $resultadoDuplicado = $this->verificarDuplicadoDetallado(
                        $competidorData['persona']['ci'],
                        $competidorData['persona']['email'],
                        $competidorData['persona']['telefono'] ?? null,
                        $area->id_area,
                        $nivel->id_nivel
                    );

                    if ($resultadoDuplicado['es_duplicado']) {
                        $competidoresDuplicados[] = [
                            'indice' => $index,
                            'nombre' => $competidorData['persona']['nombre'] . ' ' . $competidorData['persona']['apellido'],
                            'ci' => $competidorData['persona']['ci'],
                            'email' => $competidorData['persona']['email'],
                            'telefono' => $competidorData['persona']['telefono'] ?? null,
                            'area' => $area->nombre,
                            'nivel' => $nivel->nombre,
                            'campos_duplicados' => $resultadoDuplicado['campos_duplicados'],
                            'motivo' => $resultadoDuplicado['mensaje'],
                            'competidor_existente' => $resultadoDuplicado['competidor_existente']
                        ];
                        continue;
                    }

                    // 6. Preparar datos para el Service
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
                        'nombre_tutor' => $competidorData['competidor']['nombre_tutor'] ?? null,
                        'contacto_tutor' => $competidorData['competidor']['contacto_tutor'] ?? null,
                        'contacto_emergencia' => $competidorData['competidor']['contacto_emergencia'] ?? null,
                        
                        // IDs relacionales
                        'id_institucion' => $institucion->id_institucion,
                        'id_area' => $area->id_area,
                        'id_nivel' => $nivel->id_nivel,
                    ];

                    // 7. Crear competidor
                    $persona = $this->competidorService->createNewCompetidor($data);

                    // 8. Asignar al grupo si existe
                    if ($grupo) {
                        GrupoCompetidor::create([
                            'id_grupo' => $grupo->id_grupo,
                            'id_competidor' => $persona->competidor->id_competidor,
                        ]);
                    }

                    $competidoresCreados[] = $persona;

                } catch (\Exception $e) {
                    // Errores individuales por competidor
                    $competidoresConError[] = [
                        'indice' => $index,
                        'nombre' => $competidorData['persona']['nombre'] . ' ' . $competidorData['persona']['apellido'],
                        'error' => $e->getMessage()
                    ];
                    continue;
                }
            }

            DB::commit();

            $response = [
                'success' => true,
                'message' => 'Importación completada',
                'data' => [
                    'total_importados' => count($competidoresCreados),
                    'total_duplicados' => count($competidoresDuplicados),
                    'total_errores' => count($competidoresConError),
                    'competidores_creados' => $competidoresCreados
                ]
            ];

            // Agregar información de duplicados si existen
            if (count($competidoresDuplicados) > 0) {
                $response['duplicados'] = $competidoresDuplicados;
            }

            // Agregar información de errores si existen
            if (count($competidoresConError) > 0) {
                $response['errores'] = $competidoresConError;
            }

            $mensajeResumen = "El archivo ha sido importado. " . 
                count($competidoresCreados) . " competidores han sido registrados";

            if (count($competidoresDuplicados) > 0) {
                $mensajeResumen .= ", " . count($competidoresDuplicados) . " han sido omitidos por duplicidad";
            }

            if (count($competidoresConError) > 0) {
                $mensajeResumen .= ", " . count($competidoresConError) . " contienen errores y no fueron importados";
            }

            $response['message'] = $mensajeResumen;

            return response()->json($response, 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            
            $mensajeError = 'Hubo un error al importar competidores, parece existen datos duplicados o inválidos';
            $detallesError = $e->getMessage();
            
            if (str_contains($detallesError, 'Duplicate entry')) {
                $mensajeError = 'Error de duplicidad en la base de datos';
                $detallesDuplicidad = $this->analizarErrorDuplicidad($detallesError);
            } else {
                $detallesDuplicidad = null;
            }
            
            return response()->json([
                'success' => false,
                'message' => $mensajeError,
                'error' => $detallesError,
                'duplicidad_detectada' => $detallesDuplicidad
            ], 500);
        }
    }

    private function verificarDuplicadoDetallado(string $ci, string $email, ?string $telefono, int $areaId, int $nivelId): array
    {
        // Buscar competidores en la misma área y nivel
        $competidoresExistentes = Competidor::where('id_area', $areaId)
            ->where('id_nivel', $nivelId)
            ->with('persona')
            ->get();

        $camposDuplicados = [];
        $competidoresDuplicados = [];

        foreach ($competidoresExistentes as $competidor) {
            $duplicadosEnEsteCompetidor = [];
            
            if ($competidor->persona->ci === $ci) {
                $duplicadosEnEsteCompetidor[] = 'documento_identidad';
            }
            
            if ($competidor->persona->email === $email) {
                $duplicadosEnEsteCompetidor[] = 'email';
            }
            
            if ($telefono && $competidor->persona->telefono === $telefono) {
                $duplicadosEnEsteCompetidor[] = 'telefono';
            }

            if (!empty($duplicadosEnEsteCompetidor)) {
                $competidoresDuplicados[] = [
                    'competidor' => $competidor,
                    'campos' => $duplicadosEnEsteCompetidor
                ];
                
                $camposDuplicados = array_merge($camposDuplicados, $duplicadosEnEsteCompetidor);
            }
        }

        // Eliminar duplicados del array de campos
        $camposDuplicados = array_unique($camposDuplicados);
        
        if (empty($camposDuplicados)) {
            return [
                'es_duplicado' => false,
                'campos_duplicados' => [],
                'mensaje' => '',
                'competidor_existente' => null
            ];
        }

        // Construir mensaje detallado
        $mensaje = "Campos duplicados en el área '{$areaId}' y nivel '{$nivelId}': " . implode(', ', $camposDuplicados);
        
        // Agregar información de los competidores existentes
        if (!empty($competidoresDuplicados)) {
            $nombresCompetidores = [];
            foreach ($competidoresDuplicados as $dup) {
                $nombreCompleto = $dup['competidor']->persona->nombre . ' ' . $dup['competidor']->persona->apellido;
                $camposStr = implode(', ', $dup['campos']);
                $nombresCompetidores[] = "{$nombreCompleto} ({$camposStr})";
            }
            $mensaje .= ". Competidores existentes: " . implode('; ', $nombresCompetidores);
        }

        return [
            'es_duplicado' => true,
            'campos_duplicados' => $camposDuplicados,
            'mensaje' => $mensaje,
            'competidor_existente' => !empty($competidoresDuplicados) ? $competidoresDuplicados[0]['competidor'] : null
        ];
    }

    private function analizarErrorDuplicidad(string $mensajeError): array
    {
        $detalles = [];
        
        if (preg_match("/Duplicate entry '([^']+)' for key '([^']+)'/", $mensajeError, $matches)) {
            $valorDuplicado = $matches[1];
            $campoDuplicado = $matches[2];
            
            // Mapear nombres de keys de la base de datos a nombres legibles
            $camposLegibles = [
                'personas_ci_unique' => 'documento_identidad',
                'personas_email_unique' => 'email',
                'personas_telefono_unique' => 'telefono',
                'competidores_unique' => 'competidor',
                // Agregar más mapeos según sea necesario
            ];
            
            $campoLegible = $camposLegibles[$campoDuplicado] ?? $campoDuplicado;
            
            $detalles = [
                'campo' => $campoLegible,
                'valor_duplicado' => $valorDuplicado,
                'key_bd' => $campoDuplicado,
                'tipo' => 'violacion_constraint_unica'
            ];
        }
        
        return $detalles;
    }

    public function index(): JsonResponse
    {
        try {
            $competidores = $this->competidorRepository->getAllCompetidores();

            return response()->json([
                'success' => true,
                'data' => $competidores
>>>>>>> origin/develop
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
<<<<<<< HEAD
                'message' => 'Error en el servidor',
=======
                'message' => 'Error al obtener competidores',
>>>>>>> origin/develop
                'error' => $e->getMessage()
            ], 500);
        }
    }
<<<<<<< HEAD

    private function insertarFila(array $fila, int $numeroFila): array
    {
        try {
            // Buscar o crear institución (Colegio)
            $institucion = Institucion::firstOrCreate(
                ['nombre' => $fila['Colegio'] ?? 'Sin nombre'],
                [
                    'departamento' => $fila['Departamento'] ?? 'Sin departamento',
                    'tipo' => 'Unidad Educativa'
                ]
            );

            // Crear persona
            $nombreCompleto = $fila['Nombre'] ?? 'Sin nombre';
            $partesNombre = explode(' ', $nombreCompleto, 2);
            
            $persona = Persona::create([
                'nombre' => $partesNombre[0] ?? 'Sin nombre',
                'apellido' => $partesNombre[1] ?? 'Sin apellido',
                'ci' => $fila['Documento de Identidad'] ?? 'Sin CI',
                'genero' => $this->normalizarGenero($fila['Género'] ?? 'M'),
                'telefono' => $fila['Celular'] ?? 'Sin teléfono',
                'email' => $fila['E-mail'] ?? 'sin@email.com'
            ]);

            // Crear competidor
            $competidor = Competidor::create([
                'grado_escolar' => $fila['Nivel'] ?? 'Sin nivel',
                'departamento' => $fila['Departamento'] ?? 'Sin departamento',
                'contacto_tutor' => $fila['Nombre Profesor'] ?? 'Sin tutor',
                'id_persona' => $persona->id_persona,
                'id_institucion' => $institucion->id_institucion
            ]);

            return [
                'fila' => $numeroFila,
                'estado' => 'éxito',
                'persona_id' => $persona->id_persona,
                'competidor_id' => $competidor->id_competidor,
                'institucion_id' => $institucion->id_institucion
            ];

        } catch (\Exception $e) {
            return [
                'fila' => $numeroFila,
                'estado' => 'error',
                'error' => $e->getMessage(),
                'datos' => $fila
            ];
        }
    }

    private function normalizarGenero(string $genero): string
    {
        $genero = strtoupper(trim($genero));
        
        if ($genero === 'MASCULINO' || $genero === 'M') return 'M';
        if ($genero === 'FEMENINO' || $genero === 'F') return 'F';
        
        return 'M'; // Valor por defecto
    }
=======
>>>>>>> origin/develop
}