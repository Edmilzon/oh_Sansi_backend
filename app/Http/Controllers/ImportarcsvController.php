<?php

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
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en el servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

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
}