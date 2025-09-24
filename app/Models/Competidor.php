<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competidor extends Model
{
    use HasFactory;

    protected $table = 'competidor';
    protected $primaryKey = 'id_competidor';

    protected $fillable = [
        'grado_escolar', 'departamento', 'contacto_tutor', 'contacto_emergencia', 'id_persona', 'id_institucion'
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'id_institucion');
    }
}