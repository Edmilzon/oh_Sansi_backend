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

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area', 'id_area');
    }

    public function nivel()
    {
        return $this->belongsTo(Nivel::class, 'id_nivel', 'id_nivel');
    }

    public function olimpiada()
    {
        return $this->belongsTo(Olimpiada::class, 'id_olimpiada', 'id_olimpiada');
    }

    public function responsablesArea()
    {
        return $this->hasMany(ResponsableArea::class, 'id_area_nivel', 'id_area_nivel');
    }

    public function evaluadoresAn()
    {
        return $this->hasMany(EvaluadorAn::class, 'id_area_nivel', 'id_area_nivel');
    }
}
