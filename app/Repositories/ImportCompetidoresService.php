<?php

namespace App\Services;

use App\Repositories\CompetidorRepository;
use App\Model\Olimpiada;
use App\Model\ArchivoCsv;
use Illuminate\Http\UploadedFile;
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

            $resultado['total'] = count($registros);

            foreach ($registros as $index => $fila) {
                try {
                    $this->procesarFila($fila, $olimpiada, $archivoCsv, $index + 2);
                    $resultado['exitosos']++;
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

            return $resultado;

        } catch (\Exception $e) {
            Log::error("Error general importando CSV: " . $e->getMessage());
            throw new \Exception("Error al procesar el archivo CSV: " . $e->getMessage());
        }
    }

    private function procesarFila(array $fila, Olimpiada $olimpiada, ArchivoCsv $archivoCsv, int $numeroFila): void
    {
        $this->validarCamposObligatorios($fila, $numeroFila);

        $datosPersona = $this->normalizarDatosPersona($fila);
        $datosCompetidor = $this->normalizarDatosCompetidor($fila);

        $this->competidorRepository->procesarTransaccion(function() use (
            $datosPersona, 
            $datosCompetidor, 
            $fila, 
            $olimpiada, 
            $archivoCsv,
            $numeroFila
        ) {
            $institucion = $this->competidorRepository->crearInstitucion($fila['nombre_del_colegio']);

            $areaNivel = $this->competidorRepository->obtenerAreaNivel(
                $fila['area_de_competencia'],
                $fila['nivel_de_competencia'],
                $olimpiada->id_olimpiada
            );

            if (!$areaNivel) {
                throw new \Exception(
                    "No se encontró la combinación de área '{$fila['area_de_competencia']}' " .
                    "y nivel '{$fila['nivel_de_competencia']}' para la olimpiada actual"
                );
            }

            if ($this->competidorRepository->competidorExiste($datosPersona['ci'], $areaNivel->id_area_nivel)) {
                throw new \Exception("El competidor con CI {$datosPersona['ci']} ya está registrado en esta área y nivel");
            }

            $persona = $this->competidorRepository->buscarOCrearPersona($datosPersona);

            $this->competidorRepository->crearCompetidor(
                $datosCompetidor,
                $institucion->id_institucion,
                $areaNivel->id_area_nivel,
                $archivoCsv->id_archivo_csv,
                $persona->id_persona
            );
        });
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

    private function normalizarDatosPersona(array $fila): array
    {
        return [
            'ci' => trim($fila['numero_de_documento_de_identidad']),
            'nombre' => trim($fila['nombres_del_olimpista']),
            'apellido' => trim($fila['apellidos_del_olimpista']),
            'genero' => $this->normalizarGenero(trim($fila['genero'])),
            'email' => trim($fila['e_mail']),
            'telefono' => isset($fila['telefono']) ? trim($fila['telefono']) : null,
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
}