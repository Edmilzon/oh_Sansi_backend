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
        'id_area_olimpiada',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function areaOlimpiada()
    {
        return $this->belongsTo(AreaOlimpiada::class, 'id_area_olimpiada', 'id_area_olimpiada');
    }

    public function area()
    {
        return $this->hasOneThrough(Area::class, AreaOlimpiada::class, 'id_area_olimpiada', 'id_area', 'id_area_olimpiada', 'id_area');
    }
}
