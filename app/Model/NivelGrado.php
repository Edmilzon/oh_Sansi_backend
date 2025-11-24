<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NivelGrado extends Model
{
    use HasFactory;

    protected $table = 'nivel_grado';
    protected $primaryKey = 'id_nivel_grado';

    protected $fillable = [
        'id_area_nivel',
        'id_grado_escolaridad',
        'activo',
    ];

    public function areaNivel()
    {
        return $this->belongsTo(\App\Model\AreaNivel::class, 'id_area_nivel');
    }

    public function gradoEscolaridad()
    {
        return $this->belongsTo(\App\Model\GradoEscolaridad::class, 'id_grado_escolaridad');
    }
}