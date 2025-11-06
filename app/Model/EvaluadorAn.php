<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluadorAn extends Model
{
    use HasFactory;

    protected $table = 'evaluador_an';
    protected $primaryKey = 'id_evaluadorAN';

    protected $fillable = [
        'id_usuario',
        'id_area_nivel',
    ];

    public function usuario()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function areaNivel()
    {
        return $this->belongsTo(\App\Models\AreaNivel::class, 'id_area_nivel', 'id_area_nivel');
    }
}
