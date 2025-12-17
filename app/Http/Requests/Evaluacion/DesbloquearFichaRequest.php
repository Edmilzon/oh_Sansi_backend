<?php

namespace App\Http\Requests\Evaluacion;

use Illuminate\Foundation\Http\FormRequest;

class DesbloquearFichaRequest extends FormRequest
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
            'user_id.required' => 'Debe identificarse para liberar la ficha.',
            'user_id.exists'   => 'El usuario solicitante no es válido.',
        ];
    }
}
