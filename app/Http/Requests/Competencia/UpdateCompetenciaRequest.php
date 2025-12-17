<?php

namespace App\Http\Requests\Competencia;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompetenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_inicio' => ['sometimes', 'date'],
            'fecha_fin' => ['sometimes', 'date', 'after_or_equal:fecha_inicio'],
            'criterio_clasificacion' => ['sometimes', 'in:suma_ponderada,promedio_simple,manual'],
        ];
    }
}
