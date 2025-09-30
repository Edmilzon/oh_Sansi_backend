<?php

namespace App\Http\Requests;

use App\Models\Area;
use App\Models\CodigoEvaluador;
use App\Models\Nivel;
use App\Models\Persona;
use App\Models\Usuario;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreEvaluadorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'ci' => 'required|string|unique:persona,ci',
            'email' => 'required|email|unique:persona,email',

            // Datos de Usuario
            'password' => 'required|string|min:8|confirmed',

            // Código de acceso
            'codigo_evaluador' => 'required|string|exists:codigo_evaluador,codigo,activo,1',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ci.unique' => 'El número de CI ya está registrado.',
            'email.unique' => 'El correo electrónico ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'codigo_evaluador.exists' => 'El código de evaluador proporcionado no es válido o no está activo.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        $extra = [];

        // Si el error es por email ya registrado, buscamos área y nivel
        if (isset($errors['email'])) {
            $email = $this->input('email');
            $persona = Persona::where('email', $email)->first();
            if ($persona) {
                $usuario = Usuario::where('id_persona', $persona->id_persona)->first();
                if ($usuario && $usuario->id_codigo_evaluador) {
                    $codigoEvaluador = CodigoEvaluador::find($usuario->id_codigo_evaluador);
                    if ($codigoEvaluador) {
                        $area = Area::find($codigoEvaluador->id_area);
                        $nivel = Nivel::find($codigoEvaluador->id_nivel);
                        $extra['area'] = $area ? $area->nombre : null;
                        $extra['nivel'] = $nivel ? $nivel->nombre : null;
                    }
                }
            }
        }

        throw new HttpResponseException(response()->json([
            'errors' => $errors,
            'area' => $extra['area'] ?? null,
            'nivel' => $extra['nivel'] ?? null,
        ], 422));
    }
}