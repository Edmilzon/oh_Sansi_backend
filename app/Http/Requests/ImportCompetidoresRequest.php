<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportCompetidoresRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'archivo_csv' => 'required|file|mimes:csv',
        ];
    }

    public function messages(): array
    {
        return [
            'archivo_csv.required' => 'El archivo CSV es obligatorio.',
            'archivo_csv.file' => 'El archivo debe ser válido.',
            'archivo_csv.mimes' => 'El archivo debe ser de tipo CSV.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->hasFile('archivo_csv')) {
                $this->validarEstructuraCSV($validator);
            }
        });
    }

    private function validarEstructuraCSV($validator)
    {
        $archivo = $this->file('archivo_csv');
        
        try {
            $csv = \League\Csv\Reader::createFromPath($archivo->getPathname(), 'r');
            $csv->setHeaderOffset(0);
            
            $cabeceras = $csv->getHeader();
            
            $cabecerasRequeridas = [
                'numero_de_documento_de_identidad',
                'nombres_del_olimpista', 
                'apellidos_del_olimpista',
                'genero',
                'departamento_de_procedencia',
                'nombre_del_colegio',
                'e_mail',
                'area_de_competencia',
                'nivel_de_competencia',
                'grado_de_escolaridad'
            ];
            
            $cabecerasOpcionales = [
                'telefono',
                'contacto_tutor'
            ];
            
            // Verificar cabeceras requeridas
            foreach ($cabecerasRequeridas as $cabecera) {
                if (!in_array($cabecera, $cabeceras)) {
                    $validator->errors()->add(
                        'archivo_csv', 
                        "El archivo CSV no tiene la cabecera requerida: '{$cabecera}'"
                    );
                }
            }
            
            // Verificar si hay cabeceras desconocidas
            $cabecerasPermitidas = array_merge($cabecerasRequeridas, $cabecerasOpcionales);
            foreach ($cabeceras as $cabecera) {
                if (!in_array($cabecera, $cabecerasPermitidas)) {
                    $validator->errors()->add(
                        'archivo_csv',
                        "Cabecera desconocida en el CSV: '{$cabecera}'. Cabeceras permitidas: " . 
                        implode(', ', $cabecerasPermitidas)
                    );
                }
            }
            
        } catch (\Exception $e) {
            $validator->errors()->add(
                'archivo_csv',
                'Error al leer el archivo CSV: ' . $e->getMessage()
            );
        }
    }
}