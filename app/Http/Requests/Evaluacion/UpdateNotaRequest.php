<?php

namespace App\Http\Requests\Evaluacion;

use Illuminate\Foundation\Http\FormRequest;
use App\Model\Evaluacion;
use Illuminate\Validation\Validator;

class UpdateNotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:usuario,id_usuario'],
            'nota'    => ['required', 'numeric', 'min:0'],
            'estado_participacion' => ['required', 'in:presente,ausente,descalificado_etica'],
            'motivo_cambio' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $id = $this->route('id') ?? $this->route('evaluacion');

            if (!$id) return;

            $evaluacion = Evaluacion::with('examen')->find($id);

            if (!$evaluacion) {
                $validator->errors()->add('id', 'La evaluación no existe.');
                return;
            }

            if ($this->input('estado_participacion') === 'presente') {
                $nota = (float) $this->input('nota');
                $maxima = (float) $evaluacion->examen->maxima_nota;

                if ($nota > $maxima) {
                    $validator->errors()->add('nota', "La nota corregida ($nota) supera el máximo permitido ($maxima).");
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Juez no identificado.',
            'nota.required' => 'La nueva nota es obligatoria.',
            'motivo_cambio.required' => 'Por normas de auditoría, debe explicar por qué está cambiando esta nota.',
            'motivo_cambio.min' => 'El motivo del cambio debe ser explicativo.',
        ];
    }
}
