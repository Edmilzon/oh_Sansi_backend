<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEvaluadorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'ci' => 'required|string|unique:persona,ci',
            'email' => 'required|email|unique:persona,email',

            // Datos de Usuario
            'password' => 'required|string|min:8|confirmed',

            // Código de acceso
            'codigo_evaluador' => 'required|string|exists:codigo_evaluador,codigo,activo,1',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ci.unique' => 'El número de CI ya está registrado.',
            'email.unique' => 'El correo electrónico ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'codigo_evaluador.exists' => 'El código de evaluador proporcionado no es válido o no está activo.',
        ];
    }
}