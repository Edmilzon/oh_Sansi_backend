<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    public const ROL_EVALUADOR = 'evaluador';
    public const ROL_ADMIN = 'privilegiado'; 
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
        'password', 
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function responsableArea()
    {
        return $this->hasOne(Responsable::class, 'id_persona', 'id_persona');
    }

    public function codigoEvaluador()
    {
        return $this->belongsTo(CodigoEvaluador::class, 'id_codigo_evaluador', 'id_codigo_evaluador');
    }
}