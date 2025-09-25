<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model {
    use HasFactory;

    protected $table = 'area';          // si mantienes la tabla en singular
    protected $primaryKey = 'id_area';  // coincide con tu migración
    protected $fillable = ['nombre', 'descripcion', 'activo'];
}

