<?php

namespace App\Http\Requests\ConfiguracionAccion;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConfiguracionAccionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:usuario,id_usuario'],
            'accionesPorFase' => ['required', 'array', 'min:1'],
            'accionesPorFase.*.id_accion_sistema' => ['required', 'integer', 'exists:accion_sistema,id_accion_sistema'],
            'accionesPorFase.*.id_fase_global'    => ['required', 'integer', 'exists:fase_global,id_fase_global'],
            'accionesPorFase.*.habilitada'        => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'La identidad del usuario (user_id) es obligatoria.',
            'user_id.exists'   => 'El usuario especificado no existe.',
            'accionesPorFase.required' => 'No se enviaron datos de configuración.',
            'accionesPorFase.array'    => 'El formato de configuración es inválido.',
            'accionesPorFase.*.id_accion_sistema.exists' => 'Una de las acciones enviadas no es válida.',
            'accionesPorFase.*.id_fase_global.exists'    => 'Una de las fases enviadas no es válida.',
        ];
    }
}
