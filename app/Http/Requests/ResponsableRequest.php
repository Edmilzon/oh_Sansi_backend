<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;


class ResponsableRequest extends FormRequest
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
            'password' => 'required|string|min:8',
            'areas' => 'required|array|min:1',
            'areas.*' => 'integer|exists:area,id_area',

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
        ];
    }
}