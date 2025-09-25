<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    // Definir constantes para los roles
    public const ROL_EVALUADOR = 'evaluador';
    public const ROL_ADMIN = 'admin'; // Ejemplo de otro rol
    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'nombre',
        'password', // Permitir asignación masiva para 'password'
        'rol',
        'id_persona',
        'id_codigo_evaluador',
        'id_codigo_encargado',
    ];

    protected $hidden = [
        'password', // Ocultar el campo 'password' de las respuestas JSON
    ];

    /**
     * Mutator para hashear la contraseña automáticamente al asignarla.
     * El nombre del método debe ser set<Campo>Attribute.
     */
    public function setPasswordAttribute($value)
    {
        // Hashear el valor y asignarlo al campo 'password' de la base de datos.
        $this->attributes['password'] = Hash::make($value);
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }
}