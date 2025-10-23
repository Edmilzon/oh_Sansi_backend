<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodigoEvaluador extends Model
{
    use HasFactory;

    protected $table = 'codigo_evaluador';
    protected $primaryKey = 'id_codigo_evaluador';

    protected $fillable = [
        'codigo',
        'activo',
        'id_area',
        'id_nivel',
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
}