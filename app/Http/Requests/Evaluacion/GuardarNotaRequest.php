<?php

namespace App\Http\Requests\Evaluacion;

use Illuminate\Foundation\Http\FormRequest;
use App\Model\Evaluacion;
use Illuminate\Validation\Validator;

class GuardarNotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:usuario,id_usuario'],
            'nota' => ['required', 'numeric', 'min:0'],
            'estado_participacion' => ['required', 'in:presente,ausente,descalificado_etica'],
            'observacion' => ['nullable', 'string', 'max:255'],
            'motivo_cambio' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $idEvaluacion = $this->route('id');

            if (!$idEvaluacion) return;

            $evaluacion = Evaluacion::with('examen')->find($idEvaluacion);

            if (!$evaluacion) {
                $validator->errors()->add('id', 'La evaluación solicitada no existe.');
                return;
            }

            if ($this->input('estado_participacion') === 'presente') {
                $nota = (float) $this->input('nota');
                $maxima = (float) $evaluacion->examen->maxima_nota;

                if ($nota > $maxima) {
                    $validator->errors()->add('nota', "La nota ingresada ($nota) excede el máximo permitido ($maxima) para este examen.");
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'No se ha identificado al evaluador.',
            'nota.required' => 'El campo nota es obligatorio.',
            'nota.numeric' => 'La nota debe ser un valor numérico.',
            'nota.min' => 'La nota no puede ser negativa.',
            'estado_participacion.required' => 'Debe seleccionar el estado de participación (Presente/Ausente).',
            'estado_participacion.in' => 'El estado seleccionado no es válido.',
        ];
    }
}
