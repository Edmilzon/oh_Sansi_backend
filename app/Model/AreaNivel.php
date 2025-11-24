<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaNivel extends Model
{
    use HasFactory;

    protected $table = 'area_nivel';
    protected $primaryKey = 'id_area_nivel';

    protected $fillable = [
        'id_area',
        'id_nivel',
        'id_olimpiada',
        'activo',
    ];

    public function area()
    {
        return $this->belongsTo(\App\Model\Area::class, 'id_area');
    }

    public function nivel()
    {
        return $this->belongsTo(\App\Model\Nivel::class, 'id_nivel');
    }

    public function olimpiada()
    {
        return $this->belongsTo(\App\Model\Olimpiada::class, 'id_olimpiada');
    }

    public function parametro()
    {
        return $this->hasOne(\App\Model\Parametro::class, 'id_area_nivel');
    }

    public function nivelGrado()
    {
        return $this->hasMany(\App\Model\NivelGrado::class, 'id_area_nivel');
    }

    public function registroNota()
    {
        return $this->hasMany(\App\Model\RegistroNota::class, 'id_area_nivel');
    }

    public function competidor()
    {
        return $this->hasMany(\App\Model\Competidor::class, 'id_area_nivel');
    }

    public function fase()
    {
        return $this->hasMany(\App\Model\Fase::class, 'id_area_nivel');
    }

    public function evaluadorAn()
    {
        return $this->hasMany(\App\Model\EvaluadorAn::class, 'id_area_nivel');
    }
}
