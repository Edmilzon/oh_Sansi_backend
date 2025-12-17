<?php

namespace App\Http\Requests\Competencia;

use Illuminate\Foundation\Http\FormRequest;
use App\Model\CronogramaFase;
use Carbon\Carbon;

class StoreCompetenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_fase_global' => ['required', 'exists:fase_global,id_fase_global'],
            'id_area_nivel' => ['required', 'exists:area_nivel,id_area_nivel'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'criterio_clasificacion' => ['required', 'in:suma_ponderada,promedio_simple,manual'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $idFase = $this->input('id_fase_global');
            $inicio = Carbon::parse($this->input('fecha_inicio'));

            $cronograma = CronogramaFase::where('id_fase_global', $idFase)->first();

            if ($cronograma) {
                $inicioGlobal = Carbon::parse($cronograma->fecha_inicio);
                $finGlobal = Carbon::parse($cronograma->fecha_fin);

                if (!$inicio->between($inicioGlobal, $finGlobal)) {
                    $validator->errors()->add('fecha_inicio',
                        "La fecha de inicio debe estar dentro del cronograma de la fase global ({$inicioGlobal->toDateString()} al {$finGlobal->toDateString()})."
                    );
                }
            }
        });
    }
}
