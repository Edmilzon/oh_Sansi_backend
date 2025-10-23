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
            'password' => 'required|string|min:8',
            'areas_niveles' => 'required|array|min:1',
            'areas_niveles.*.area' => 'required|integer|exists:area,id_area',
            'areas_niveles.*.niveles' => 'required|array|min:1',
            'areas_niveles.*.niveles.*' => 'required|integer|exists:nivel,id_nivel'
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
            'codigo_evaluador.exists' => 'El código de evaluador proporcionado no es válido o no está activo.',
        ];
    }
}