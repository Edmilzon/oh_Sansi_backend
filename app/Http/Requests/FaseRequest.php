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
            'Nota_minima_clasificacion' => 'nullable|numeric|min:0|max:100',
            'cantidad_maxima_de_clasificados' => 'nullable|integer|min:1',
            'nombre' => 'nullable|string|max:255',
            'orden' => 'nullable|integer|min:1',
            'descripcion' => 'nullable|string'
        ];
    }
}