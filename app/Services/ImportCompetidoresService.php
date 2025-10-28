<?php

namespace App\Services;

use App\Repositories\CompetidorRepository;
use App\Model\Olimpiada;
use App\Model\ArchivoCsv;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use League\Csv\Statement;

class ImportCompetidoresService
{
    protected $competidorRepository;
    protected $olimpiadaService;

    public function __construct(CompetidorRepository $competidorRepository, OlimpiadaService $olimpiadaService)
    {
        $this->competidorRepository = $competidorRepository;
        $this->olimpiadaService = $olimpiadaService;
    }

    public function importarCSV(UploadedFile $archivo): array
    {
        $resultado = [
            'total' => 0,
            'exitosos' => 0,
            'errores' => 0,
            'errores_detalle' => []
        ];

        DB::beginTransaction();
        
        try {
            $olimpiada = $this->olimpiadaService->obtenerOlimpiadaActual();
            
            $archivoCsv = $this->competidorRepository->crearArchivoCsv(
                $archivo->getClientOriginalName(),
                $olimpiada->id_olimpiada
            );

            $csv = Reader::createFromPath($archivo->getPathname(), 'r');
            $csv->setHeaderOffset(0);
            $csv->setDelimiter(',');

            $registros = (new Statement())->process($csv);
            $filas = iterator_to_array($registros);
            
            $resultado['total'] = count($filas);

            $instituciones = $this->precargarInstituciones($filas);
            $areasNiveles = $this->precargarAreasNiveles($filas, $olimpiada->id_olimpiada);
            $personasExistentes = $this->precargarPersonasExistentes($filas);
            $competidoresExistentes = $this->precargarCompetidoresExistentes($areasNiveles);

            $registrosProcesar = [];
            
            foreach ($filas as $index => $fila) {
                try {
                    $this->validarCamposObligatorios($fila, $index + 2);
                    
                    $datosProcesados = $this->prepararRegistro(
                        $fila, 
                        $instituciones, 
                        $areasNiveles, 
                        $personasExistentes,
                        $competidoresExistentes,
                        $index + 2
                    );
                    
                    if ($datosProcesados) {
                        $registrosProcesar[] = $datosProcesados;
                        $resultado['exitosos']++;
                    }
                    
                } catch (\Exception $e) {
                    $resultado['errores']++;
                    $resultado['errores_detalle'][] = [
                        'fila' => $index + 2,
                        'error' => $e->getMessage(),
                        'datos' => $fila
                    ];
                    Log::error("Error importando competidor fila {$index}: " . $e->getMessage());
                }
            }

            if (!empty($registrosProcesar)) {
                $this->insertarEnLote($registrosProcesar, $archivoCsv->id_archivo_csv);
            }

            DB::commit();
            return $resultado;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error general importando CSV: " . $e->getMessage());
            throw new \Exception("Error al procesar el archivo CSV: " . $e->getMessage());
        }
    }

    private function precargarInstituciones(array $filas): array
    {
        $nombresInstituciones = array_unique(array_column($filas, 'nombre_del_colegio'));
        $nombresInstituciones = array_map('trim', $nombresInstituciones);
        
        $institucionesExistentes = Institucion::whereIn('nombre', $nombresInstituciones)
            ->pluck('id_institucion', 'nombre')
            ->toArray();
            
        $instituciones = [];
        
        foreach ($nombresInstituciones as $nombre) {
            if (!isset($institucionesExistentes[$nombre])) {
                $nuevaInstitucion = Institucion::create(['nombre' => $nombre]);
                $institucionesExistentes[$nombre] = $nuevaInstitucion->id_institucion;
            }
            $instituciones[$nombre] = $institucionesExistentes[$nombre];
        }
        
        return $instituciones;
    }

    private function precargarAreasNiveles(array $filas, int $idOlimpiada): array
    {
        $combinaciones = [];
        
        foreach ($filas as $fila) {
            $area = trim($fila['area_de_competencia']);
            $nivel = trim($fila['nivel_de_competencia']);
            $combinaciones[$area . '|' . $nivel] = ['area' => $area, 'nivel' => $nivel];
        }
        
        $areasNiveles = [];
        
        foreach ($combinaciones as $combinacion) {
            $areaNivel = $this->competidorRepository->obtenerAreaNivel(
                $combinacion['area'],
                $combinacion['nivel'],
                $idOlimpiada
            );
            
            if ($areaNivel) {
                $areasNiveles[$combinacion['area'] . '|' . $combinacion['nivel']] = $areaNivel->id_area_nivel;
            }
        }
        
        return $areasNiveles;
    }

    private function precargarPersonasExistentes(array $filas): array
    {
        $cis = array_unique(array_map(function($fila) {
            return trim($fila['numero_de_documento_de_identidad']);
        }, $filas));
        
        return Persona::whereIn('ci', $cis)
            ->pluck('id_persona', 'ci')
            ->toArray();
    }

    private function precargarCompetidoresExistentes(array $areasNiveles): array
    {
        return Competidor::whereIn('id_area_nivel', array_values($areasNiveles))
            ->with('persona')
            ->get()
            ->groupBy('id_area_nivel')
            ->map(function($competidores) {
                return $competidores->pluck('persona.ci')->toArray();
            })
            ->toArray();
    }

    private function prepararRegistro(
        array $fila, 
        array $instituciones, 
        array $areasNiveles,
        array $personasExistentes,
        array $competidoresExistentes,
        int $numeroFila
    ): ?array {
        $ci = trim($fila['numero_de_documento_de_identidad']);
        $nombreInstitucion = trim($fila['nombre_del_colegio']);
        $area = trim($fila['area_de_competencia']);
        $nivel = trim($fila['nivel_de_competencia']);
        
        $claveAreaNivel = $area . '|' . $nivel;
        
        if (!isset($areasNiveles[$claveAreaNivel])) {
            throw new \Exception(
                "No se encontró la combinación de área '{$area}' y nivel '{$nivel}' para la olimpiada actual"
            );
        }
        
        $idAreaNivel = $areasNiveles[$claveAreaNivel];
        
        if (isset($competidoresExistentes[$idAreaNivel]) && 
            in_array($ci, $competidoresExistentes[$idAreaNivel])) {
            throw new \Exception("El competidor con CI {$ci} ya está registrado en esta área y nivel");
        }
        
        $datosPersona = $this->normalizarDatosPersona($fila);
        
        $idPersona = $personasExistentes[$ci] ?? null;
        $nuevaPersona = $idPersona ? null : $datosPersona;
        
        return [
            'competidor' => $this->normalizarDatosCompetidor($fila),
            'id_institucion' => $instituciones[$nombreInstitucion],
            'id_area_nivel' => $idAreaNivel,
            'id_persona' => $idPersona,
            'nueva_persona' => $nuevaPersona,
            'ci' => $ci
        ];
    }

    private function insertarEnLote(array $registros, int $idArchivoCsv): void
    {
        $nuevasPersonas = [];
        $mapaCIaID = [];
        
        foreach ($registros as $registro) {
            if ($registro['nueva_persona']) {
                $nuevasPersonas[] = $registro['nueva_persona'];
            }
        }
        
        if (!empty($nuevasPersonas)) {
            Persona::insert($nuevasPersonas);
            
            $cis = array_column($nuevasPersonas, 'ci');
            $personasInsertadas = Persona::whereIn('ci', $cis)
                ->pluck('id_persona', 'ci')
                ->toArray();
                
            $mapaCIaID = $personasInsertadas;
        }
    
        $competidoresInsertar = [];
        
        foreach ($registros as $registro) {
            $idPersona = $registro['id_persona'] ?: $mapaCIaID[$registro['ci']];
            
            $competidoresInsertar[] = array_merge($registro['competidor'], [
                'id_institucion' => $registro['id_institucion'],
                'id_area_nivel' => $registro['id_area_nivel'],
                'id_archivo_csv' => $idArchivoCsv,
                'id_persona' => $idPersona,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        Competidor::insert($competidoresInsertar);
    }

    private function normalizarDatosPersona(array $fila): array
    {
        return [
            'ci' => trim($fila['numero_de_documento_de_identidad']),
            'nombre' => trim($fila['nombres_del_olimpista']),
            'apellido' => trim($fila['apellidos_del_olimpista']),
            'genero' => $this->normalizarGenero(trim($fila['genero'])),
            'email' => trim($fila['e_mail']),
            'telefono' => isset($fila['telefono']) ? trim($fila['telefono']) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function normalizarDatosCompetidor(array $fila): array
    {
        return [
            'grado_escolar' => trim($fila['grado_de_escolaridad']),
            'departamento' => trim($fila['departamento_de_procedencia']),
            'contacto_tutor' => isset($fila['contacto_tutor']) ? trim($fila['contacto_tutor']) : null,
        ];
    }

    private function normalizarGenero(string $genero): string
    {
        $genero = strtoupper(trim($genero));
        
        if ($genero === 'MASCULINO' || $genero === 'M') return 'M';
        if ($genero === 'FEMENINO' || $genero === 'F') return 'F';
        
        return 'M';
    }

    private function validarCamposObligatorios(array $fila, int $numeroFila): void
    {
        $camposObligatorios = [
            'numero_de_documento_de_identidad' => 'Número de documento de identidad',
            'nombres_del_olimpista' => 'Nombres del olimpista',
            'apellidos_del_olimpista' => 'Apellidos del olimpista',
            'genero' => 'Género',
            'departamento_de_procedencia' => 'Departamento de procedencia',
            'nombre_del_colegio' => 'Nombre del colegio',
            'e_mail' => 'E-mail',
            'area_de_competencia' => 'Área de competencia',
            'nivel_de_competencia' => 'Nivel de competencia',
            'grado_de_escolaridad' => 'Grado de escolaridad'
        ];

        foreach ($camposObligatorios as $campo => $nombre) {
            if (empty($fila[$campo] ?? null)) {
                throw new \Exception("El campo '{$nombre}' es obligatorio (fila {$numeroFila})");
            }
        }
    }
}