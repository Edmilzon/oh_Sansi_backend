<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAreaNivelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_area' => 'sometimes|integer|exists:area,id_area',
            'id_nivel' => 'sometimes|integer|exists:nivel,id_nivel',
            'activo' => 'sometimes|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'id_area.exists' => 'El área seleccionada no existe',
            'id_nivel.exists' => 'El nivel seleccionado no existe',
            'activo.boolean' => 'El campo activo debe ser verdadero o falso'
        ];
    }
}