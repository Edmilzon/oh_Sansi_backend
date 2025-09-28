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
            //Array de competidores
            'competidores' => 'required|array|min:1',
            'competidores.*.persona.nombre' => 'required|string|max:255',
            'competidores.*.persona.apellido' => 'required|string|max:255',
            'competidores.*.persona.ci' => 'required|string|unique:persona,ci',
            'competidores.*.persona.fecha_nac' => 'required|date',
            'competidores.*.persona.genero' => 'nullable|in:M,F',
            'competidores.*.persona.telefono' => 'nullable|string|unique:persona,telefono',
            'competidores.*.persona.email' => 'required|email|unique:persona,email',

            // Datos del Competidor
            'competidores.*.competidor.grado_escolar' => 'nullable|string|max:100',
            'competidores.*.competidor.departamento' => 'nullable|string|max:100',
            'competidores.*.competidor.contacto_tutor' => 'nullable|string|max:255',
            'competidores.*.competidor.contacto_emergencia' => 'nullable|string|max:255',

            // Datos de Institución
            'competidores.*.institucion.nombre' => 'required|string|max:255',
            'competidores.*.institucion.tipo' => 'nullable|string|max:100',
            'competidores.*.institucion.departamento' => 'nullable|string|max:100',
            'competidores.*.institucion.direccion' => 'nullable|string|max:500',
            'competidores.*.institucion.telefono' => 'nullable|string|unique:institucion,telefono',

            // Datos de Grupo
            'competidores.*.grupo.nombre' => 'nullable|string|max:255',
            'competidores.*.grupo.descripcion' => 'nullable|string|max:500',
            'competidores.*.max_integrantes' => 'nullable|integer|min:1',

            // Datos Relacionales (pueden ser compartidos o individuales)
            'competidores.*.area.nombre' => 'required|string|max:255',
            'competidores.*.nivel.nombre' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'competidores.*.persona.ci.unique' => 'El número de CI ya está registrado.',
            'competidores.*.persona.telefono.unique' => 'El número de teléfono ya está registrado.',
            'competidores.*.persona.email.unique' => 'El correo electrónico ya está registrado.',
            'competidores.*institucion.telefono.unique' => 'El número de teléfono ya está registrado.',
        ];
    }
}