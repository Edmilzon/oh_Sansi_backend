<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponsableArea extends Model
{
    use HasFactory;

    protected $table = 'responsable_area';
    protected $primaryKey = 'id_responsableArea';

    protected $fillable = [
        'id_usuario',
        'id_area',
        'id_olimpiada',
    ];

    public function areaO()
    {
        return $this->belongsTo(\App\Model\Area::class, 'id_area');
    }

    public function usuario()
    {
        return $this->belongsTo(\App\Model\Usuario::class, 'id_usuario');
    }

    public function olimpiada()
    {
        return $this->belongsTo(\App\Model\Olimpiada::class, 'id_olimpiada');
    }

    public function competencias()
    {
        return $this->hasMany(\App\Model\Competencia::class, 'id_responsableArea');
    }

    public function aval()
    {
        return $this->hasMany(\App\Model\Aval::class, 'id_responsableArea');
    }
}