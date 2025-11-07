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
        'id_grado_escolaridad',
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

    public function gradoEscolaridad()
    {
        return $this->belongsTo(\App\Model\GradoEscolaridad::class, 'id_grado_escolaridad');
    }
<<<<<<< HEAD
}
=======

    public function responsablesArea()
    {
        return $this->hasMany(ResponsableArea::class, 'id_area_nivel', 'id_area_nivel');
    }

    public function evaluadoresAn()
    {
        return $this->hasMany(EvaluadorAn::class, 'id_area_nivel', 'id_area_nivel');
    }

    public function parametros()
    {
        return $this->hasMany(Parametro::class, 'id_area_nivel', 'id_area_nivel');
    }
}
>>>>>>> 3460f4866543e1a4671ce141dc24312a53b2db1b
