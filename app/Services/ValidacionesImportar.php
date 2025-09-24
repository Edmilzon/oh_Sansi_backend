<?php

namespace App\Services;

use app\Models\Persona;
use app\Models\Institucion;
use app\Models\Competidor;
use app\Models\Area;
use app\Models\Nivel;
use app\Models\Usuario;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use League\Csv\Statement;

class ProcesadorCSVService
{
    private $errores = [];
    private $cabecerasEsperadas = [
        'N°',
        'Documento de Identidad',
        'Nombre',
        'Género',
        'Departamento', 
        'Colegio',
        'Celular',
        'E-mail',
        'Area',
        'Nivel',
        'Nombre Profesor',
        'Responsable',
        'Nivel del Profesor'
    ];

    private $cabecerasAbreviadas = [
        'doc_identidad' => 'Documento de Identidad',
        'nombre' => 'Nombre',
        'genero' => 'Género',
        'depto' => 'Departamento',
        'colegio' => 'Colegio', 
        'celular' => 'Celular',
        'email' => 'E-mail',
        'area' => 'Area',
        'nivel' => 'Nivel',
        'profesor' => 'Nombre Profesor',
        'responsable' => 'Responsable',
        'nivel_profesor' => 'Nivel del Profesor'
    ];

    public function procesarArchivo(UploadedFile $archivo): array
    {
        $this->errores = [];
        
        //Validar csv
        $csv = Reader::createFromPath($archivo->getPathname(), 'r');
        $csv->setHeaderOffset(0);
        
        $registros = Statement::create()->process($csv);
        $cabecerasArchivo = $csv->getHeader();

        //Validar cabeceras
        $this->validarCabeceras($cabecerasArchivo);

        if (!empty($this->errores)) {
            return $this->generarReporte(0, 0, 0, $this->errores, []);
        }

        $procesados = [];
        $contador = 0;

        foreach ($registros as $numeroFila => $fila) {
            $contador++;
            $filaNumero = $numeroFila + 2;

            //Validar datos de la fila
            $erroresFila = $this->validarFila($fila, $filaNumero);
            
            if (empty($erroresFila)) {
                try {
                    $procesado = $this->procesarFila($fila, $filaNumero);
                    if ($procesado) {
                        $procesados[] = $procesado;
                    }
                } catch (\Exception $e) {
                    $this->errores[] = [
                        'fila' => $filaNumero,
                        'tipo' => 'procesamiento',
                        'campo' => 'sistema',
                        'error' => $e->getMessage(),
                        'valor' => 'N/A'
                    ];
                }
            } else {
                $this->errores = array_merge($this->errores, $erroresFila);
            }
        }

        //Insertar BD
        $insertados = [];
        if (empty($this->errores)) {
            $insertados = $this->insertarEnBaseDatos($procesados);
        }

        return $this->generarReporte($contador, count($procesados), count($this->errores), $this->errores, $insertados);
    }

    private function validarCabeceras(array $cabecerasArchivo): void
    {
        $cabecerasArchivo = array_map('trim', $cabecerasArchivo);
        $cabecerasEsperadas = array_map('trim', $this->cabecerasEsperadas);

        //Verificar si faltan cabeceras
        $faltantes = array_diff($cabecerasEsperadas, $cabecerasArchivo);
        
        foreach ($faltantes as $faltante) {
            $this->errores[] = [
                'fila' => 1, // Fila de cabeceras
                'tipo' => 'cabecera',
                'campo' => $faltante,
                'error' => 'Cabecera faltante',
                'valor' => 'N/A'
            ];
        }

        //Verificar abreviaciones de cabeceras
        foreach ($cabecerasArchivo as $cabecera) {
            if (!in_array($cabecera, $cabecerasEsperadas) && 
                !in_array($cabecera, array_keys($this->cabecerasAbreviadas))) {
                $this->errores[] = [
                    'fila' => 1,
                    'tipo' => 'cabecera',
                    'campo' => $cabecera,
                    'error' => 'Cabecera no reconocida',
                    'valor' => 'N/A'
                ];
            }
        }
    }

    private function validarFila(array $fila, int $numeroFila): array
    {
        $errores = [];

        $campos = $this->mapearCampos($fila);

        //Validaciones por campo
        $reglas = [
            'Documento de Identidad' => 'required|string|max:20|unique:persona,ci',
            'Nombre' => 'required|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'Género' => 'required|in:M,F,m,f,Masculino,Femenino',
            'Departamento' => 'required|string|max:100',
            'Colegio' => 'required|string|max:255',
            'Celular' => 'required|string|max:15|regex:/^[0-9+\-\s()]+$/',
            'E-mail' => 'required|email|max:255|unique:persona,email',
            'Area' => 'required|string|max:100',
            'Nivel' => 'required|string|max:50',
            'Nombre Profesor' => 'required|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'Responsable' => 'required|string|max:255',
            'Nivel del Profesor' => 'required|in:Primaria,Secundaria'
        ];

        foreach ($reglas as $campo => $regla) {
            if (isset($campos[$campo])) {
                $valor = $campos[$campo];
                $validacion = Validator::make(
                    [$campo => $valor], 
                    [$campo => $regla],
                    $this->mensajesError()
                );

                if ($validacion->fails()) {
                    $errores[] = [
                        'fila' => $numeroFila,
                        'tipo' => 'dato',
                        'campo' => $campo,
                        'error' => $validacion->errors()->first($campo),
                        'valor' => $valor
                    ];
                }
            } else {
                $errores[] = [
                    'fila' => $numeroFila,
                    'tipo' => 'estructura',
                    'campo' => $campo,
                    'error' => 'Campo faltante en la fila',
                    'valor' => 'N/A'
                ];
            }
        }

        return $errores;
    }

    private function mapearCampos(array $fila): array
    {
        $camposMapeados = [];
        
        foreach ($fila as $cabecera => $valor) {
            $cabecera = trim($cabecera);
            $valor = trim($valor);
            
            //Buscar cabecera completa
            if (in_array($cabecera, $this->cabecerasEsperadas)) {
                $camposMapeados[$cabecera] = $valor;
            } 
            //Buscar cabecera abreviada
            elseif (isset($this->cabecerasAbreviadas[$cabecera])) {
                $campoCompleto = $this->cabecerasAbreviadas[$cabecera];
                $camposMapeados[$campoCompleto] = $valor;
            }
        }
        
        return $camposMapeados;
    }

    private function mensajesError(): array
    {
        return [
            'required' => 'El campo es obligatorio',
            'string' => 'Debe ser texto',
            'numeric' => 'Debe ser numérico',
            'email' => 'Formato de email inválido',
            'unique' => 'El valor ya existe en el sistema',
            'in' => 'Valor no permitido',
            'regex' => 'Formato incorrecto',
            'max' => 'Excede el máximo de caracteres permitidos'
        ];
    }

    private function procesarFila(array $fila, int $numeroFila): ?array
    {
        $campos = $this->mapearCampos($fila);

        //Buscar o crear institución (Colegio)
        $institucion = Institucion::firstOrCreate(
            ['nombre' => $campos['Colegio']],
            [
                'departamento' => $campos['Departamento'],
                'tipo' => 'Unidad Educativa'
            ]
        );

        // Buscar o crear área
        $area = Area::firstOrCreate(
            ['nombre' => $campos['Area']],
            ['descripcion' => 'Área de ' . $campos['Area']]
        );

        // Buscar o crear nivel
        $nivel = Nivel::firstOrCreate(
            ['nombre' => $campos['Nivel']],
            ['descripcion' => 'Nivel ' . $campos['Nivel']]
        );

        // Procesar género
        $genero = $this->normalizarGenero($campos['Género']);

        return [
            'persona_estudiante' => [
                'nombre' => $this->extraerNombre($campos['Nombre']),
                'apellido' => $this->extraerApellido($campos['Nombre']),
                'ci' => $campos['Documento de Identidad'],
                'genero' => $genero,
                'telefono' => $campos['Celular'],
                'email' => $campos['E-mail']
            ],
            'competidor' => [
                'grado_escolar' => $campos['Nivel'],
                'departamento' => $campos['Departamento'],
                'contacto_tutor' => $campos['Nombre Profesor'],
                'id_institucion' => $institucion->id_institucion
            ],
            'profesor' => [
                'nombre' => $campos['Nombre Profesor'],
                'nivel' => $campos['Nivel del Profesor']
            ],
            'responsable' => [
                'nombre' => $campos['Responsable']
            ],
            'institucion' => $institucion->toArray(),
            'area' => $area->toArray(),
            'nivel' => $nivel->toArray()
        ];
    }

    private function normalizarGenero(string $genero): string
    {
        $genero = strtoupper(trim($genero));
        
        if ($genero === 'MASCULINO') return 'M';
        if ($genero === 'FEMENINO') return 'F';
        if (in_array($genero, ['M', 'F'])) return $genero;
        
        return 'M'; // Valor por defecto
    }

    private function extraerNombre(string $nombreCompleto): string
    {
        $partes = explode(' ', $nombreCompleto);
        return $partes[0] ?? $nombreCompleto;
    }

    private function extraerApellido(string $nombreCompleto): string
    {
        $partes = explode(' ', $nombreCompleto);
        return count($partes) > 1 ? implode(' ', array_slice($partes, 1)) : '';
    }

    private function insertarEnBaseDatos(array $procesados): array
    {
        return DB::transaction(function () use ($procesados) {
            $insertados = [];

            foreach ($procesados as $datos) {
                // Crear persona (estudiante)
                $personaEstudiante = Persona::create($datos['persona_estudiante']);

                // Crear competidor
                $competidor = Competidor::create([
                    'grado_escolar' => $datos['competidor']['grado_escolar'],
                    'departamento' => $datos['competidor']['departamento'],
                    'contacto_tutor' => $datos['competidor']['contacto_tutor'],
                    'id_persona' => $personaEstudiante->id_persona,
                    'id_institucion' => $datos['competidor']['id_institucion']
                ]);

                $insertados[] = [
                    'persona_estudiante' => $personaEstudiante->toArray(),
                    'competidor' => $competidor->toArray(),
                    'institucion' => $datos['institucion'],
                    'area' => $datos['area'],
                    'nivel' => $datos['nivel']
                ];
            }

            return $insertados;
        });
    }

    private function generarReporte(int $totalFilas, int $exitosas, int $fallidas, array $errores, array $insertados): array
    {
        return [
            'resumen' => [
                'total_filas_procesadas' => $totalFilas,
                'filas_exitosas' => $exitosas,
                'filas_fallidas' => $fallidas,
                'porcentaje_exito' => $totalFilas > 0 ? round(($exitosas / $totalFilas) * 100, 2) : 0
            ],
            'errores_detallados' => $errores,
            'registros_insertados' => [
                'total_personas' => count($insertados),
                'total_competidores' => count($insertados),
                'detalles' => $insertados
            ],
            'timestamp' => now()->toDateTimeString(),
            'estado' => empty($errores) ? 'completado' : 'con_errores'
        ];
    }
}