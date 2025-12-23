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
            'configuraciones' => ['required', 'array', 'min:1'],
            'configuraciones.*.id_configuracion_accion' => ['required', 'integer', 'exists:configuracion_accion,id_configuracion_accion'],
            'configuraciones.*.habilitada' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'La identidad del usuario (user_id) es obligatoria.',
            'configuraciones.required' => 'No se enviaron datos de configuración.',
            'configuraciones.array'    => 'El formato de configuración debe ser un array.',
            'configuraciones.*.id_configuracion_accion.exists' => 'Uno de los registros de configuración no existe en la base de datos.',
        ];
    }
}
