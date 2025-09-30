<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    protected $table = 'persona';
    protected $primaryKey = 'id_persona';

    protected $fillable = [
        'nombre',
        'apellido',
        'ci',
        'fecha_nac',
        'genero',
        'telefono',
        'email',
    ];

    protected $casts = [
        'fecha_nac' => 'date',
    ];

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'id_persona', 'id_persona');
    }

    public function evaluador()
    {
        return $this->hasOne(Evaluador::class, 'id_persona', 'id_persona');
    }

    public function competidor()
    {
        return $this->hasOne(Competidor::class, 'id_persona', 'id_persona');
    }

    public function responsableArea()
    {
        return $this->hasOne(Responsable::class, 'id_persona', 'id_persona');
    }
}