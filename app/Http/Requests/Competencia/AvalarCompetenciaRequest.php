<?php

namespace App\Http\Requests\Competencia;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use App\Model\Usuario;

class AvalarCompetenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password_confirmacion' => ['required', 'string'],
            'user_id_simulado'      => ['nullable', 'integer', 'exists:usuario,id_usuario'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = $this->user();

            if (!$user && $this->input('user_id_simulado')) {
                $user = Usuario::find($this->input('user_id_simulado'));
            }

            if (!$user) {
                $validator->errors()->add('auth', 'Error de Seguridad: No se identificó al usuario. Envía el Token en el Header o el "user_id_simulado" en el body.');
                return;
            }

            if (!Hash::check($this->input('password_confirmacion'), $user->password)) {
                $validator->errors()->add('password_confirmacion', 'La contraseña es incorrecta. No se puede avalar.');
            }
        });
    }
}
