<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Datos del Competidor(Persona)
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'ci' => 'required|string|unique:competidor,ci',
            'grado_escolar' => 'required|string|max:100',
            'telefono' => 'nullable|string|unique:competidor,telefono',
            'email' => 'required|email|unique:competidor,email',
            'area.nombre'=>'required|string|max:255',
            'nivel.nombre'=>'required|string|max:255',

        ];
    }

    public function messages(): array
    {
        return [
            'ci.unique' => 'El número de CI ya está registrado.',
            'telefono.unique:persona' => 'El número de teléfono ya está registrado.',
            'email.unique' => 'El correo electrónico ya está registrado.',
            'institucion.telefono.unique' => 'El número de teléfono ya está registrado.',
        ];
    }
}