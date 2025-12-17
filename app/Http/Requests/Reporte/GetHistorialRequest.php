<?php

namespace App\Http\Requests\Reporte;

use Illuminate\Foundation\Http\FormRequest;

class GetHistorialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            // Como el ID viene en la ruta (URL), aquí podrías validar filtros opcionales
            // Ejemplo: ?desde=2025-01-01
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
        ];
    }

    /**
     * Preparar datos para validación (Opcional)
     * Útil si quieres validar el ID de la ruta explícitamente aquí
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);
    }
}
