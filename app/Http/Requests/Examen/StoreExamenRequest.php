<?php

namespace App\Http\Requests\Examen;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidarReglasExamen;
use App\Model\Examen;
use Illuminate\Validation\Validator;

class StoreExamenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_competencia' => ['required', 'exists:competencia,id_competencia'],
            'nombre' => ['required', 'string', 'max:255'],
            'ponderacion' => ['required', 'numeric', 'min:0', 'max:100'],
            'maxima_nota' => ['required', 'numeric', 'min:1'],
            'fecha_hora_inicio' => ['nullable', 'date'],
            'tipo_regla' => ['nullable', 'in:nota_corte'],
            'configuracion_reglas' => ['nullable', 'array', new ValidarReglasExamen($this->input('tipo_regla'))],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $idCompetencia = $this->input('id_competencia');
            $nuevaPonderacion = $this->input('ponderacion');

            if ($idCompetencia) {
                $sumaActual = Examen::where('id_competencia', $idCompetencia)->sum('ponderacion');

                if (($sumaActual + $nuevaPonderacion) > 100) {
                    $validator->errors()->add('ponderacion',
                        "Error matemático: La suma de ponderaciones superaría el 100% (Actual: {$sumaActual} + Nuevo: {$nuevaPonderacion} = " . ($sumaActual + $nuevaPonderacion) . "%)."
                    );
                }
            }
        });
    }
}
