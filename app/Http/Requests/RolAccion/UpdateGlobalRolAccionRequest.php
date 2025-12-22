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
            'roles.*.acciones' => ['present', 'array'],
            'roles.*.acciones.*.id_accion_sistema' => ['required', 'integer', 'exists:accion_sistema,id_accion_sistema'],
            'roles.*.acciones.*.activo' => ['required', 'boolean'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'user_id.required' => 'Es necesario identificar al usuario administrador (user_id).',
            'roles.required' => 'No se enviaron datos de roles para actualizar.',
            'roles.array' => 'El formato de roles es inválido.',
            'roles.*.id_rol.exists' => 'Uno de los roles enviados no existe en el sistema.',
            'roles.*.acciones.*.activo.boolean' => 'El valor de activo debe ser verdadero o falso.',
        ];
    }
}
