<?php

namespace App\Http\Requests\Fase;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Model\Olimpiada;

class StoreFaseCompletaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $olimpiada = Olimpiada::where('estado', 1)->first();
        $idOlimpiada = $olimpiada ? $olimpiada->id_olimpiada : null;

        return [
            'nombre' => 'required|string|max:50',
            'codigo' => [
                'required',
                Rule::in(['CONFIGURACION', 'EVALUACION', 'FINAL'])
            ],
            'orden' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('fase_global', 'orden')
                    ->where(function ($query) use ($idOlimpiada) {
                        return $query->where('id_olimpiada', $idOlimpiada);
                    })
            ],
            'fecha_inicio' => 'required|date|after_or_equal:now',
            'fecha_fin'    => 'required|date|after:fecha_inicio',
            'activar_ahora'=> 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.in' => 'El código seleccionado no es válido.',
            'orden.unique' => 'Ya existe una fase con este número de orden en la gestión actual. Por favor elija otro.',
            'fecha_inicio.after_or_equal' => 'La fecha de inicio no puede estar en el pasado.',
            'fecha_fin.after' => 'La fecha de finalización debe ser posterior a la de inicio.'
        ];
    }
}
