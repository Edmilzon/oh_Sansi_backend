<?php

namespace App\Http\Requests\Reporte;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GetHistorialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page'        => ['required', 'integer', 'min:1'],
            'limit'       => ['required', 'integer', 'min:1', 'max:1000'],
            'id_area'     => ['nullable', 'integer', 'exists:area,id_area'],
            'ids_niveles' => ['nullable', 'string', 'regex:/^(\d+,)*\d+$/'],
            'search'      => ['nullable', 'string', 'min:3', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'page.required'       => 'El número de página es obligatorio.',
            'limit.required'      => 'El límite de registros por página es obligatorio.',
            'limit.max'           => 'No puedes solicitar más de 1000 registros por página.',
            'ids_niveles.regex'   => 'El formato de niveles debe ser IDs separados por comas (ej: 1,2,3).',
            'id_area.exists'      => 'El área seleccionada no existe.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Parámetros de consulta inválidos.',
            'errors'  => $validator->errors()
        ], 422));
    }
}
