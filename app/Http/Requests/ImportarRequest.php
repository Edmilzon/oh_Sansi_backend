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
            // Datos del Competidor(como Persona)
            'persona.nombre' => 'required|string|max:255',
            'persona.apellido' => 'required|string|max:255',
            'persona.ci' => 'required|string|unique:persona,ci',
            'persona.fecha_nac' => 'required|date',
            'persona.genero' => 'nullable|in:M,F',
            'persona.telefono' => 'nullable|string|unique:persona,telefono',
            'persona.email' => 'required|email|unique:persona,email',

            //Datos del Competidor
            'competidor.grado_escolar' => 'nullable|string|max:100',
            'competidor.departamento' => 'nullable|string|max:100',
            'competidor.contacto_tutor' => 'nullable|string|max:255',
            'competidor.contacto_emergencia' => 'nullable|string|max:255',

            //Datos de Institución
            'institucion.nombre' => 'required|string|max:255',
            'institucion.tipo' => 'nullable|string|max:100',
            'institucion.departamento' => 'nullable|string|max:100',
            'institucion.direccion' => 'nullable|string|max:500',
            'institucion.telefono' => 'nullable|string|unique:institucion,telefono',

            //Datos de Grupo
            'grupo.nombre' => 'nullable|string|max:255',
            'grupo.descripcion' => 'nullable|string|max:500',
            'max_integrantes' => 'nullable|integer|min:1',

            //Datos Relacionales
            'area.nombre' => 'required|string|max:255',
            'nivel.nombre' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'persona.ci.unique' => 'El número de CI ya está registrado.',
            'persona.telefono.unique' => 'El número de teléfono ya está registrado.',
            'persona.email.unique' => 'El correo electrónico ya está registrado.',
            'institucion.telefono.unique' => 'El número de teléfono ya está registrado.',
        ];
    }
}