<?php

namespace App\Http\Requests\Evaluacion;

use Illuminate\Foundation\Http\FormRequest;

class BloquearFichaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:usuario,id_usuario'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Error de seguridad: No se pudo identificar al juez solicitante.',
            'user_id.exists'   => 'El usuario evaluador no existe en la base de datos.',
        ];
    }
}
