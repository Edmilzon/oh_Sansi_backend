<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportCompetidoresRequest;
use App\Services\ImportCompetidoresService;
use Illuminate\Http\JsonResponse;

class CompetidorImportController extends Controller
{
    protected $importService;

    public function __construct(ImportCompetidoresService $importService)
    {
        $this->importService = $importService;
    }

    public function importar(ImportCompetidoresRequest $request): JsonResponse
    {
        try {
            $resultado = $this->importService->importarCSV($request->file('archivo_csv'));

            $mensaje = "Importación completada: " .
                      "{$resultado['exitosos']} de {$resultado['total']} registros procesados exitosamente.";

            if ($resultado['errores'] > 0) {
                $mensaje .= " {$resultado['errores']} registros tuvieron errores.";
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'data' => $resultado
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al importar el archivo: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function plantilla(): JsonResponse
    {
        $cabeceras = [
            'numero_de_documento_de_identidad',
            'nombres_del_olimpista',
            'apellidos_del_olimpista',
            'genero',
            'departamento_de_procedencia',
            'nombre_del_colegio',
            'e_mail',
            'area_de_competencia',
            'nivel_de_competencia',
            'grado_de_escolaridad',
            'telefono',
            'contacto_tutor',
        ];

        return response()->json([
            'success' => true,
            'message' => 'Estructura del archivo CSV',
            'data' => [
                'cabeceras' => $cabeceras,
                'ejemplo' => [
                    '1234567',
                    'Juan',
                    'Perez Garcia',
                    'M',
                    'La Paz',
                    'Colegio San Calixto',
                    'juan.perez@email.com',
                    'Matemáticas',
                    'Secundaria',
                    '6to de secundaria',
                    '77712345',
                    '77754321',
                ],
                'notas' => [
                    'Los campos de área y nivel deben existir previamente en la base de datos',
                    'Debe existir la relación área-nivel para la olimpiada actual',
                    'El género debe ser M (Masculino) o F (Femenino)',
                    'Los últimos 2 campos son opcionales',
                    'El sistema verificará duplicados por CI, área y nivel'
                ]
            ]
        ]);
    }
}