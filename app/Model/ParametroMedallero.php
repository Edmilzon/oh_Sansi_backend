<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParametroMedallero extends Model
{
    use HasFactory;

    protected $table = 'param_medallero';
    protected $primaryKey = 'id_param_medallero';

    protected $fillable = [
        'id_area_nivel',
        'oro',
        'plata',
        'bronce',
        'menciones',
    ];

    public function areaNivel()
    {
        return $this->belongsTo(AreaNivel::class, 'id_area_nivel', 'id_area_nivel');
    }
}
