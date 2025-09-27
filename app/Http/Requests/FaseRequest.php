<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->rol === 'responsable_area';
    }

    public function rules(): array
    {
        return [
            'Nota_minima_clasificacion' => 'required|numeric|min:0|max:100',
            'cantidad_maxima_de_clasificados' => 'required|integer|min:1',
            'nombre' => 'required|string|max:255',
            'orden' => 'required|integer|min:1',
            'descripcion' => 'nullable|string'
        ];
    }
}