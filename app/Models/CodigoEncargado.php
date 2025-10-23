<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodigoEncargado extends Model
{
    use HasFactory;

    protected $table = 'codigo_encargado';
    protected $primaryKey = 'id_codigo_encargado';

    protected $fillable = [
        'codigo',
        'descripcion',
        'id_area',
    ];
}