<?php

namespace App\Http\Requests\Cronograma;

use Illuminate\Foundation\Http\FormRequest;
use App\Model\CronogramaFase;
use Carbon\Carbon;

class UpdateCronogramaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $rutaParam = $this->route('cronograma_fase') ?? $this->route('cronograma');
        $cronograma = null;

        if ($rutaParam instanceof CronogramaFase) {
            $cronograma = $rutaParam;
        } elseif (is_numeric($rutaParam)) {
            $cronograma = CronogramaFase::find($rutaParam);
        }

        if ($cronograma) {

            $inicio = $this->input('fecha_inicio');
            $fin = $this->input('fecha_fin');

            $this->merge([
                'fecha_inicio' => $inicio ?? $cronograma->fecha_inicio?->format('Y-m-d H:i:s'),
                'fecha_fin'    => $fin    ?? $cronograma->fecha_fin?->format('Y-m-d H:i:s'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'id_fase_global' => ['sometimes', 'integer', 'exists:fase_global,id_fase_global'],
            'fecha_inicio'   => ['sometimes', 'date'],
            'fecha_fin'      => ['sometimes', 'date', 'after:fecha_inicio'],
            'descripcion'    => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_fin.after' => 'La fecha y hora de finalización debe ser posterior al inicio.',
        ];
    }
}
