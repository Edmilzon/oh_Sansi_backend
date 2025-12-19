<?php

namespace App\Http\Requests\Fase;

use Illuminate\Foundation\Http\FormRequest;
use App\Model\FaseGlobal;

class UpdateFaseCronogramaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $idFase = $this->route('id');

        if ($idFase) {
            $fase = FaseGlobal::with('cronograma')->find($idFase);

            if ($fase && $fase->cronograma) {
                $cronograma = $fase->cronograma;
                $this->merge([
                    'fecha_inicio' => $this->input('fecha_inicio', $cronograma->fecha_inicio?->format('Y-m-d H:i:s')),
                    'fecha_fin'    => $this->input('fecha_fin', $cronograma->fecha_fin?->format('Y-m-d H:i:s')),
                ]);
            }
        }
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
    {
        return [
            'fecha_inicio' => ['sometimes', 'date'],
            'fecha_fin'    => ['sometimes', 'date', 'after:fecha_inicio'],
            'estado'       => ['sometimes', 'boolean', 'in:0,1'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_fin.after' => 'La fecha de finalización debe ser posterior al inicio.',
            'estado.boolean'  => 'El estado debe ser verdadero (1) o falso (0).',
        ];
    }
}
