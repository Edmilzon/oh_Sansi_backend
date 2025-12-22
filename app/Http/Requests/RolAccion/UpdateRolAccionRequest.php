<?php

namespace App\Http\Requests\RolAccion;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGlobalRolAccionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:usuario,id_usuario'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*.id_rol' => ['required', 'integer', 'exists:rol,id_rol'],
            'roles.*.acciones' => ['required', 'array'],
            'roles.*.acciones.*.id_accion_sistema' => ['required', 'integer', 'exists:accion_sistema,id_accion_sistema'],
            'roles.*.acciones.*.activo' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'roles.required' => 'Se requiere la matriz de roles para actualizar.',
            'roles.*.acciones.required' => 'Faltan las acciones para uno de los roles.',
        ];
    }
}
