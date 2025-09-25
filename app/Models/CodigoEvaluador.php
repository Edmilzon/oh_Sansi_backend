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
    ];
}