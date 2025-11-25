<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;
    
    protected $table = 'area';
    protected $primaryKey = 'id_area';
    
    protected $fillable = [
        'nombre',
    ];

    public function niveles()
    {
        return $this->belongsToMany(Nivel::class, 'area_nivel', 'id_area', 'id_nivel')
                    ->withPivot('id_olimpiada', 'activo')
                    ->withTimestamps();
    }

    public function areaNiveles()
    {
        return $this->hasMany(AreaNivel::class, 'id_area', 'id_area');
    }

    public function responsableArea()
    {
        return $this->hasMany(ResponsableArea::class, 'id_area', 'id_area');
    }
}
