<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroNota extends Model
{
    use HasFactory;

    protected $table = 'registro_nota';
    protected $primaryKey = 'id_registro_nota';

    protected $fillable = [
        'id_area_nivel',
        'id_evaluadorAN',
        'id_competidor',
        'accion',
        'nota_anterior',
        'nota_nueva',
        'observacion',
        'descripcion',
    ];

    public function areaNivel()
    {
        return $this->belongsTo(\App\Model\AreaNivel::class, 'id_area_nivel');
    }
    
    public function evaluadorAN()
    {
        return $this->belongsTo(\App\Model\EvaluadorAreaNivel::class, 'id_evaluadorAN');
    }
    
    public function competidor()
    {
        return $this->belongsTo(\App\Model\Competidor::class, 'id_competidor');
    }
}