<?php

namespace App\Http\Requests\Competencia;

use Illuminate\Foundation\Http\FormRequest;
use App\Model\Competencia;
use Illuminate\Validation\Validator;

class ConcluirCompetenciaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Validaciones de integridad del negocio antes de llamar al servicio.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $id = $this->route('id');

            $competencia = Competencia::with('examenes')->find($id);

            if (!$competencia) {
                $validator->errors()->add('id', 'La competencia solicitada no existe.');
                return;
            }

            if ($competencia->estado_fase !== 'en_proceso') {
                $validator->errors()->add('estado_fase', "No se puede concluir la competencia. Solo se permiten cierres en estado 'en_proceso'. Estado actual: {$competencia->estado_fase}");
            }

            foreach ($competencia->examenes as $examen) {
                if ($examen->estado_ejecucion !== 'finalizada') {
                    $validator->errors()->add('examenes', "Bloqueo de seguridad: El examen '{$examen->nombre}' (ID: {$examen->id_examen}) aún no ha sido finalizado. Debe cerrar todas las mesas antes de concluir la competencia.");
                }
            }
        });
    }
}
