<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidarReglasExamen implements ValidationRule
{
    public function __construct(protected ?string $tipoRegla) {}
    
    /**
     * @param  \Closure(string, ?array<string, string>): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value) || empty($this->tipoRegla)) {
            return;
        }

        $config = is_string($value) ? json_decode($value, true) : $value;

        if (!is_array($config)) {
            $fail("El formato de configuración no es válido.");
            return;
        }

        if ($this->tipoRegla === 'nota_corte') {
            if (!isset($config['nota_minima'])) {
                $fail("La regla 'Nota de Corte' requiere el campo 'nota_minima'.");
            } elseif (!is_numeric($config['nota_minima']) || $config['nota_minima'] < 0 || $config['nota_minima'] > 100) {
                $fail("La 'nota_minima' debe ser un número entre 0 y 100.");
            }
        }
    }
}
