<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fase extends Model
{
    use HasFactory;

    protected $table = 'fase';
    protected $primaryKey = 'id_fase';

    protected $fillable = [
        'Nota_minima_clasificacion',
        'cantidad_maxima_de_clasificados',
        'nombre',
        'orden',
        'descripcion'
    ];
}