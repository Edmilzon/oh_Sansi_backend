<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FaseRequest extends FormRequest
{
    /*public function authorize(): bool
    {
        return auth()->check() && auth()->user()->rol === 'responsable_area';
    }*/

    public function rules(): array
    {
        return [
            'Nota_maxima_clasificacion' => 'nullable|numeric|min:0|max:100',
            'Nota_minima_clasificacion' => 'nullable|numeric|min:0|max:100',
            'cantidad_maxima_de_clasificados' => 'nullable|integer|min:1',
            'id_area' => 'required|exists:area,id_area',
            'niveles' => 'required|array|min:1',
            'niveles.*' => 'integer|exists:nivel,id_nivel',
        ];
    }
}