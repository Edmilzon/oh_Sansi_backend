<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreParametroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'area_niveles' => 'required|array|min:1',
            'area_niveles.*.id_area_nivel' => 'required|integer|exists:area_nivel,id_area_nivel',
            'area_niveles.*.nota_max_clasif' => 'required|numeric|min:0',
            'area_niveles.*.nota_min_clasif' => 'required|numeric|min:0',
            'area_niveles.*.cantidad_max_apro' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'area_niveles.required' => 'Debe seleccionar al menos un área-nivel',
            'area_niveles.*.id_area_nivel.required' => 'El ID del área-nivel es requerido',
            'area_niveles.*.id_area_nivel.exists' => 'El área-nivel seleccionado no existe',
            'area_niveles.*.nota_max_clasif.required' => 'La nota máxima de clasificación es requerida',
            'area_niveles.*.nota_min_clasif.required' => 'La nota mínima de clasificación es requerida',
            'area_niveles.*.cantidad_max_apro.required' => 'La cantidad máxima de aprobados es requerida',
        ];
    }
}