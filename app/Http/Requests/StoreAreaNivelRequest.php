<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAreaNivelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_area' => 'required|integer|exists:area,id_area',
            'id_nivel' => 'required|integer|exists:nivel,id_nivel',
            'activo' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'id_area.required' => 'El área es requerida',
            'id_area.exists' => 'El área seleccionada no existe',
            'id_nivel.required' => 'El nivel es requerido',
            'id_nivel.exists' => 'El nivel seleccionado no existe',
            'activo.boolean' => 'El campo activo debe ser verdadero o falso'
        ];
    }
}