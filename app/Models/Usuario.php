<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'nombre',
        'password',
        'rol',
        'id_persona',
        'id_codigo_evaluador',
        'id_codigo_encargado',
    ];

    protected $hidden = [
        'password_hash',
    ];

    /**
     * Mutator para hashear la contraseña automáticamente al asignarla.
     * El nombre del método debe ser set<Campo>Attribute.
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password_hash'] = Hash::make($value);
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }
}